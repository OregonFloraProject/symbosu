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
    header: path.join(SRC_DIR, 'header', 'main.jsx'),
    footer: path.join(SRC_DIR, 'footer', 'main.jsx'),
    home: path.join(SRC_DIR, 'home', 'main.jsx'),
    newsletters: path.join(SRC_DIR, 'home', 'newsletters.jsx'),
    whatsnew: path.join(SRC_DIR, 'home', 'whatsnew.jsx'),
    garden: path.join(SRC_DIR, 'garden', 'main.jsx'),
    rare: path.join(SRC_DIR, 'rare', 'main.jsx'),
    inventory: path.join(SRC_DIR, 'inventory', 'main.jsx'),
    identify: path.join(SRC_DIR, 'identify', 'identify.jsx'),
    taxa: path.join(SRC_DIR, 'taxa', 'main.jsx'),
    'taxa-search': path.join(SRC_DIR, 'taxa', 'search.jsx'),
    'taxa-garden': path.join(SRC_DIR, 'taxa', 'taxa-garden.jsx'),
    'taxa-rare': path.join(SRC_DIR, 'taxa', 'taxa-rare.jsx'),
    explore: path.join(SRC_DIR, 'explore', 'explore.jsx'),
    'explore-vendor': path.join(SRC_DIR, 'explore', 'explore-vendor.jsx'),
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
