const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const CSSMinimizerPlugin = require('css-minimizer-webpack-plugin');
const exec = require('child_process').exec;

const SRC_DIR = path.resolve(__dirname, 'src');
const REACT_OUT_DIR = path.resolve(__dirname, 'dist');
const CSS_OUT_DIR = path.resolve(__dirname, '..', '..', 'css', 'compiled');

const commonConfig = {
  context: path.resolve(__dirname, 'src'),
  mode: process.env.NODE_ENV === 'development' ? 'development' : 'production',
  watch: process.env.NODE_ENV === 'development',
  stats: {
    colors: true,
    assets: true,
    all: false,
  },
  watchOptions: {
    ignored: /node_modules/,
  },
};

const reactConfig = {
  entry: {
    header: {
      import: path.join(SRC_DIR, 'header', 'main.jsx'),
    },
    footer: {
      import: path.join(SRC_DIR, 'footer', 'main.jsx'),
      dependOn: 'header',
    },
    home: {
      import: path.join(SRC_DIR, 'home', 'main.jsx'),
      dependOn: 'header',
    },
    newsletters: {
      import: path.join(SRC_DIR, 'home', 'newsletters.jsx'),
      dependOn: 'header',
    },
    whatsnew: {
      import: path.join(SRC_DIR, 'home', 'whatsnew.jsx'),
      dependOn: 'header',
    },
    'checklist-special': {
      import: path.join(SRC_DIR, 'checklist-special', 'main.jsx'),
      dependOn: 'header',
    },
    inventory: {
      import: path.join(SRC_DIR, 'inventory', 'main.jsx'),
      dependOn: 'header',
    },
    identify: {
      import: path.join(SRC_DIR, 'identify', 'identify.jsx'),
      dependOn: 'header',
    },
    taxa: {
      import: path.join(SRC_DIR, 'taxa', 'main.jsx'),
      dependOn: 'header',
    },
    'taxa-search': {
      import: path.join(SRC_DIR, 'taxa', 'search.jsx'),
      dependOn: 'header',
    },
    'taxa-garden': {
      import: path.join(SRC_DIR, 'taxa', 'taxa-garden.jsx'),
      dependOn: 'header',
    },
    'taxa-rare': {
      import: path.join(SRC_DIR, 'taxa', 'taxa-rare.jsx'),
      dependOn: 'header',
    },
    explore: {
      import: path.join(SRC_DIR, 'explore', 'explore.jsx'),
      dependOn: 'header',
    },
    'explore-vendor': {
      import: path.join(SRC_DIR, 'explore', 'explore-vendor.jsx'),
      dependOn: 'header',
    },
  },
  output: {
    path: REACT_OUT_DIR,
  },
  optimization: {
    minimizer: [new TerserJSPlugin()],
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        },
      },
    ],
  },
  stats: 'errors-warnings',
};

const lessConfig = {
  entry: {
    theme: path.join(SRC_DIR, 'less', 'theme.less'),
    header: path.join(SRC_DIR, 'less', 'header.less'),
    footer: path.join(SRC_DIR, 'less', 'footer.less'),
    garden: path.join(SRC_DIR, 'less', 'garden.less'),
    rare: path.join(SRC_DIR, 'less', 'rare.less'),
    taxa: path.join(SRC_DIR, 'less', 'taxa.less'),
    inventory: path.join(SRC_DIR, 'less', 'inventory.less'),
  },
  output: {
    path: CSS_OUT_DIR,
  },
  plugins: [
    new MiniCssExtractPlugin(),
    // Remove the stupid JS files that are generated when LESS is compiled
    {
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap('CleanCssPlugin', () => {
          exec(`rm -f ${CSS_OUT_DIR}/*.js`, (err, stdout, stderr) => {
            if (err) {
              console.error(stderr);
            }
            console.log(stdout);
          });
        });
      },
    },
  ],
  optimization: {
    minimizer: [new CSSMinimizerPlugin()],
  },
  module: {
    rules: [
      {
        test: /\.(le|c)ss$/,
        exclude: /node_modules/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
          },
          {
            loader: 'css-loader',
            options: { url: false },
          },
          'less-loader',
        ],
      },
    ],
  },
  stats: {
    errors: true,
    assets: true,
    warnings: true,
    entrypoints: false,
    modules: false,
  },
};

module.exports = [Object.assign({}, commonConfig, reactConfig), Object.assign({}, commonConfig, lessConfig)];
