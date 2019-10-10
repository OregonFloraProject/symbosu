const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

const OUT_DIR = path.resolve(__dirname, "dist");

module.exports = {
  context: path.resolve(__dirname, "src"),
  mode: process.env.NODE_ENV === "development" ? "development" : "production",
  entry: {
    header: path.resolve(__dirname, "src", "header", "main.jsx"),
    garden: path.resolve(__dirname, "src", "garden", "main.jsx"),
    gardenTaxa: path.resolve(__dirname, "src", "gardenTaxa", "main.jsx"),
    main: path.resolve(__dirname, "src", "less", "main.less")
  },
  output: {
    path: OUT_DIR
  },
  watch: process.env.NODE_ENV === "development",
  watchOptions: {
    ignored: /node_modules/
  },
  plugins: [
    new MiniCssExtractPlugin()
  ],
  optimization: {
    minimizer: [new TerserJSPlugin(), new OptimizeCSSAssetsPlugin()]
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        }
      },
      {
        test: /\.(less|css)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: path.join(OUT_DIR, "css"),
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