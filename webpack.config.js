const fs = require("fs");

const HtmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

const mode = process.env.NODE_ENV || "production";
const devMode = mode === "development";

// Create an entry and HtmlWebpackPlugin(s) for each AtoM plugin folder with
// "webpack.entry.js" and "templates/_layout_start_webpack.php" files.
var entry = {};
var htmlPlugins = [];
fs.readdirSync(__dirname + "/plugins")
  .filter(
    (plugin) =>
      fs.existsSync(__dirname + "/plugins/" + plugin + "/webpack.entry.js") &&
      fs.existsSync(
        __dirname +
          "/plugins/" +
          plugin +
          "/templates/_layout_start_webpack.php"
      )
  )
  .forEach((plugin) => {
    entry[plugin] = "./plugins/" + plugin + "/webpack.entry.js";
    // Layout start template for all plugins
    templates = [
      "./plugins/" + plugin + "/templates/_layout_start_webpack.php",
    ];
    // Include error and unavailable templates for arDominionB5Plugin
    if (plugin === "arDominionB5Plugin") {
      templates.push(
        "./config/unavailableB5_webpack.php",
        "./config/error/errorB5_webpack.html.php"
      );
    }

    templates.forEach((path) =>
      htmlPlugins.push(
        new HtmlWebpackPlugin({
          template: path,
          filename: "." + path.replace("_webpack", ""),
          publicPath: "/assets",
          chunks: [plugin],
          inject: false,
          minify: false,
        })
      )
    );
  });

module.exports = {
  mode: mode,
  entry: entry,
  output: {
    path: __dirname + "/assets",
    filename: "../plugins/[name]/build/js/bundle.[contenthash].js",
  },
  devtool: devMode ? "eval-source-map" : "source-map",
  module: {
    rules: [
      {
        test: /\.(sa|sc|c)ss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "resolve-url-loader",
          { loader: "sass-loader", options: { sourceMap: true } },
        ],
      },
    ],
  },
  plugins: htmlPlugins.concat([
    new MiniCssExtractPlugin({
      filename: "../plugins/[name]/build/css/bundle.[contenthash].css",
    }),
  ]),
};
