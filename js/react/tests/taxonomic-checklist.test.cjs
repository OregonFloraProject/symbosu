/* global __dirname */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');
const babel = require('@babel/core');

const componentPath = path.resolve(__dirname, '../src/taxonomic-checklist/main.jsx');
const source = fs.readFileSync(componentPath, 'utf8');
const compiled = babel.transformSync(source, {
  filename: componentPath,
  presets: ['@babel/preset-env', '@babel/preset-react'],
}).code;

const Fragment = Symbol('Fragment');

function createHarness() {
  const state = [];
  let hookIndex = 0;
  let root;
  let componentType;
  let effectCleanup;
  const timers = new Map();
  let nextTimer = 1;
  const header = { getAttribute: () => JSON.stringify({ clientRoot: '/flora' }) };
  const container = {};
  const React = {
    Fragment,
    createElement(type, props, ...children) {
      return { type, props: { ...props, children: children.flat() } };
    },
    useState(initialValue) {
      const index = hookIndex++;
      if (!(index in state)) state[index] = initialValue;
      return [state[index], (value) => {
        state[index] = typeof value === 'function' ? value(state[index]) : value;
      }];
    },
    useRef(initialValue) {
      const index = hookIndex++;
      if (!(index in state)) state[index] = { current: initialValue };
      return state[index];
    },
    useEffect(effect) {
      hookIndex++;
      effectCleanup = effect();
    },
  };
  const ReactDOM = { render: (element) => { componentType = element.type; root = element; } };
  const window = {
    setTimeout(callback, delay) {
      const id = nextTimer++;
      timers.set(id, { callback, delay });
      return id;
    },
    clearTimeout(id) { timers.delete(id); },
  };
  const document = {
    getElementById(id) { return id === 'react-header' ? header : container; },
    createElement: () => ({}),
    head: { appendChild() {} },
  };
  const pending = [];
  const fetch = (url) => new Promise((resolve, reject) => pending.push({ url, resolve, reject }));
  const module = { exports: {} };
  const requireShim = (name) => {
    if (name === 'react') return React;
    if (name === 'react-dom') return ReactDOM;
    throw new Error(`Unexpected require: ${name}`);
  };
  vm.runInNewContext(`(function(require,module,exports,document,window,fetch){${compiled}\n})`, {
    URLSearchParams,
  })(requireShim, module, module.exports, document, window, fetch);

  function render() {
    hookIndex = 0;
    const Component = module.exports.default || componentType;
    root = Component({ clientRoot: '/flora' });
    visit(root, (node) => {
      if (node.props.ref && typeof node.props.ref === 'object') {
        node.props.ref.current = { contains: (target) => target === 'inside' };
      }
    });
  }
  function visit(node, callback) {
    if (Array.isArray(node)) return node.forEach((child) => visit(child, callback));
    if (node == null || typeof node === 'boolean' || typeof node === 'string' || typeof node === 'number') return;
    callback(node);
    visit(node.props && node.props.children, callback);
  }
  function find(predicate) {
    const matches = [];
    visit(root, (node) => { if (predicate(node)) matches.push(node); });
    return matches;
  }
  function findVisible(predicate) {
    const matches = [];
    function visitVisible(node, hiddenByAncestor = false) {
      if (Array.isArray(node)) return node.forEach((child) => visitVisible(child, hiddenByAncestor));
      if (node == null || typeof node === 'boolean' || typeof node === 'string' || typeof node === 'number') return;
      const hidden = hiddenByAncestor || node.props.hidden === true;
      if (!hidden && predicate(node)) matches.push(node);
      visitVisible(node.props && node.props.children, hidden);
    }
    visitVisible(root);
    return matches;
  }
  function byClass(className) {
    return find((node) => node.props.className === className);
  }
  function text(node) {
    if (node == null || typeof node === 'boolean') return '';
    if (Array.isArray(node)) return node.map(text).join('');
    if (typeof node === 'string' || typeof node === 'number') return String(node);
    return text(node.props && node.props.children);
  }
  function click(node) {
    node.props.onClick({ preventDefault() {} });
    render();
  }
  function change(value) {
    const input = find((node) => node.type === 'input')[0];
    input.props.onChange({ target: { value } });
    render();
  }
  function runTimer(delay) {
    const entry = [...timers.entries()].find(([, timer]) => timer.delay === delay);
    assert.ok(entry, `expected a ${delay}ms timer`);
    timers.delete(entry[0]);
    entry[1].callback();
    render();
  }
  async function settle() {
    for (let index = 0; index < 8; index++) await Promise.resolve();
    render();
  }
  function resolveRequest(index, results) {
    pending[index].resolve({ ok: true, json: async () => results });
    return settle();
  }
  function results(familyCount = 7, genusCount = 6) {
    return {
      family: Array.from({ length: familyCount }, (_, i) => ({ label: `Family${i}`, family: `Family ${i}` })),
      genus: Array.from({ length: genusCount }, (_, i) => ({ label: `Genus${i}`, family: 'Rosaceae' })),
    };
  }
  render();
  return { render, find, findVisible, byClass, text, click, change, runTimer, settle, pending, resolveRequest, results, timers, unmount: () => effectCleanup && effectCleanup(), root: () => root };
}

test('sends a grouped query-only request and renders Family then Genus with preserved PDF links/content', async () => {
  const app = createHarness();
  app.change('ros');
  app.runTimer(500);
  assert.equal(app.pending.length, 1);
  assert.match(app.pending[0].url, /taxonomic-checklist\.php\?query=ros$/);
  assert.doesNotMatch(app.pending[0].url, /mode=/);
  await app.resolveRequest(0, app.results());

  const toggles = app.find((node) => node.props.className === 'fg-group-toggle');
  assert.deepEqual(toggles.map((node) => app.text(node)), ['Family', 'Genus']);
  assert.deepEqual(toggles.map((node) => [node.props['aria-expanded'], node.props['aria-controls']]), [
    [true, 'taxonomic-checklist-family-results'],
    [true, 'taxonomic-checklist-genus-results'],
  ]);
  assert.equal(app.find((node) => node.type === 'a' && node.props.className === 'dropdown-item').length, 10);
  assert.equal(app.find((node) => node.type === 'a' && node.props.href === '/flora/pages/pdfs/family/Family%200.pdf').length, 1);
  assert.ok(app.find((node) => node.type === 'p').some((node) => app.text(node).includes('The Oregon Vascular Plant Checklist')));
});

test('collapse and More controls affect only their group, then reset for a new query', async () => {
  const app = createHarness();
  app.change('ros'); app.runTimer(500); await app.resolveRequest(0, app.results(20, 20));
  const toggles = () => app.find((node) => node.props.className === 'fg-group-toggle');
  const visibleLinks = () => app.findVisible((node) => node.type === 'a' && node.props.className === 'dropdown-item');
  const visibleMore = () => app.findVisible((node) => node.props.className === 'fg-more');
  const visibleLinkCount = (group) => visibleLinks().filter((node) => app.text(node).startsWith(group)).length;
  assert.equal(visibleLinkCount('Family'), 5);
  assert.equal(visibleLinkCount('Genus'), 5);
  assert.equal(visibleMore().length, 2);

  app.click(toggles()[0]);
  assert.deepEqual(toggles().map((node) => node.props['aria-expanded']), [false, true]);
  const sections = app.find((node) => node.type === 'section' && node.props.className === 'fg-result-group');
  assert.equal(sections[0].props['aria-label'], 'Family results');
  assert.equal(visibleLinkCount('Family'), 0);
  assert.equal(visibleLinkCount('Genus'), 5);
  assert.deepEqual(visibleMore().map((node) => node.props.className), ['fg-more']);
  app.click(toggles()[0]);
  assert.equal(visibleLinkCount('Family'), 5);
  app.click(visibleMore()[0]);
  assert.equal(visibleLinkCount('Family'), 20);
  assert.equal(visibleLinkCount('Genus'), 5);
  assert.equal(visibleMore().length, 1);
  app.click(visibleMore()[0]);
  assert.equal(visibleLinkCount('Family'), 20);
  assert.equal(visibleLinkCount('Genus'), 20);
  assert.equal(visibleMore().length, 0);

  app.change('rosa');
  assert.equal(toggles().length, 0);
  app.runTimer(500);
  await app.resolveRequest(1, app.results(20, 20));
  assert.deepEqual(toggles().map((node) => node.props['aria-expanded']), [true, true]);
  assert.equal(visibleLinkCount('Family'), 5);
  assert.equal(visibleLinkCount('Genus'), 5);
  assert.equal(visibleMore().length, 2);
});

test('unmount cleanup cancels timers and ignores outstanding responses', async () => {
  const app = createHarness();
  app.change('ros');
  app.runTimer(500);
  assert.equal(app.pending.length, 1);
  app.unmount();
  assert.equal(app.timers.size, 0);
  await app.resolveRequest(0, app.results(1, 1));
  assert.equal(app.find((node) => node.props.className === 'fg-result-group').length, 0);
});

test('shows an empty-group message when one group has results and hides suggestions when both are empty', async () => {
  const app = createHarness();
  app.change('ros'); app.runTimer(500);
  await app.resolveRequest(0, { family: [{ label: 'Rosaceae', family: 'Rosaceae' }], genus: [] });
  assert.ok(app.find((node) => node.props.className === 'fg-no-matches').some((node) => app.text(node) === 'No genus matches.'));
  app.change('xyz'); app.runTimer(500);
  await app.resolveRequest(1, { family: [], genus: [] });
  assert.equal(app.find((node) => node.props.className === 'fg-result-group').length, 0);
});

test('rejects stale responses and clears request errors after four seconds', async () => {
  const app = createHarness();
  app.change('ros'); app.runTimer(500);
  app.change('rosa'); app.runTimer(500);
  await app.resolveRequest(0, app.results(1, 1));
  assert.equal(app.find((node) => node.props.className === 'fg-result-group').length, 0);
  app.pending[1].reject(new Error('offline'));
  await app.settle();
  assert.ok(app.find((node) => node.props.className === 'fg-confirmation').some((node) => app.text(node) === 'Search is temporarily unavailable.'));
  app.runTimer(4000);
  assert.equal(app.text(app.find((node) => node.props.className === 'fg-confirmation')[0]), '');
});

test('supports focus search and keeps keyboard focus within the widget from dismissing suggestions', async () => {
  const app = createHarness();
  const input = app.find((node) => node.type === 'input')[0];
  input.props.onFocus();
  app.render();
  assert.equal(app.pending.length, 0);
  app.change('ros'); app.runTimer(500); await app.resolveRequest(0, app.results(1, 1));
  const wrapper = app.find((node) => node.props.className === 'search-wrapper')[0];
  wrapper.props.onBlur({ relatedTarget: 'inside' });
  wrapper.props.onFocusCapture();
  app.render();
  assert.equal(app.timers.size, 0);
  wrapper.props.onBlur({ relatedTarget: null });
  app.render();
  app.runTimer(150);
  assert.equal(app.find((node) => node.props.className === 'fg-result-group').length, 0);
});
