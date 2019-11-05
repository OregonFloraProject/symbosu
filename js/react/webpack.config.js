const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const exec = require('child_process').exec;

const SRC_DIR = path.resolve(__dirname, "src");
const REACT_OUT_DIR = path.resolve(__dirname, "dist");
const CSS_OUT_DIR = path.resolve(__dirname, "..", "..", "css", "compiled");

const commonConfig = {
  context: path.resolve(__dirname, "src"),
  mode: process.env.NODE_ENV === "development" ? "development" : "production",
  watch: process.env.NODE_ENV === "development",
  watchOptions: {
    ignored: /node_modules/
  }
};

const reactConfig = {
  entry: {
    header: path.join(SRC_DIR, "header", "main.jsx"),
    footer: path.join(SRC_DIR, "footer", "main.jsx"),
    garden: path.join(SRC_DIR, "garden", "main.jsx"),
    gardenTaxa: path.join(SRC_DIR, "gardenTaxa", "main.jsx"),
    taxa: path.join(SRC_DIR, "taxa", "main.jsx")
  },
  output: {
    path: REACT_OUT_DIR
  },
  optimization: {
    minimizer: [new TerserJSPlugin()]
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        }
      }
    ]
  }
};

const lessConfig = {
  entry: {
    theme: path.join(SRC_DIR, "less", "theme.less"),
    header: path.join(SRC_DIR, "less", "header.less"),
    footer: path.join(SRC_DIR, "less", "footer.less"),
    garden: path.join(SRC_DIR, "less", "garden.less"),
    taxa: path.join(SRC_DIR, "less", "taxa.less")
  },
  output: {
    path: CSS_OUT_DIR
  },
  plugins: [
    new MiniCssExtractPlugin(),
    // Remove the stupid JS files that are generated when LESS is compiled
    {
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap("CleanCssPlugin", () => {
          exec(`rm -f ${CSS_OUT_DIR}/*.js`, (err, stdout, stderr) => {
            if (err) { console.error(stderr); }
            console.log(stdout);
          })
        })
      }
    }
  ],
  optimization: {
    minimizer: [new OptimizeCSSAssetsPlugin()]
  },
  module: {
    rules: [
      {
        test: /\.(less|css)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: path.join(REACT_OUT_DIR, "css"),
              hmr: process.env.NODE_ENV === "development"
            }
          },
          "css-loader",
          "less-loader"
        ]
      }
    ]
  }
};

module.exports = [
  Object.assign({}, commonConfig, reactConfig),
  Object.assign({}, commonConfig, lessConfig)
];