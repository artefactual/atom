const fs = require("fs");

const HtmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

const mode = process.env.NODE_ENV || "production";
const devMode = mode === "development";

var entry = {
  vendor: {
    import: [
      "jquery/dist/jquery",
      "bootstrap/dist/js/bootstrap.bundle",
      "bootstrap-autocomplete/dist/latest/bootstrap-autocomplete",
      "imagesloaded/imagesloaded.pkgd",
      "masonry-layout/dist/masonry.pkgd",
      "mediaelement/build/mediaelement-and-player",
      "@accessible360/accessible-slick/slick/slick",
      "jquery-expander/jquery.expander",
      "jquery-mousewheel/jquery.mousewheel",
      "jquery-ui-dist/jquery-ui",
      "jstree/dist/jstree",
    ],
    filename: "js/[name].bundle.[contenthash].js",
  },
};

// Create an entry and HtmlWebpackPlugin(s) for each AtoM plugin folder with
// "webpack.entry.js" and "templates/_layout_start_webpack.php" files.
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
          publicPath: "/dist",
          chunks: ["vendor", plugin],
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
    path: __dirname + "/dist",
    filename: "js/[name].bundle.[contenthash].js",
    clean: true,
  },
  devtool: devMode ? "eval-source-map" : "source-map",
  module: {
    rules: [
      {
        test: require.resolve("jquery"),
        loader: "expose-loader",
        options: {
          exposes: ["$", "jQuery"],
        },
      },
      {
        test: require.resolve("bootstrap/dist/js/bootstrap.bundle"),
        loader: "expose-loader",
        options: {
          exposes: ["bootstrap"],
        },
      },
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
      filename: "css/[name].bundle.[contenthash].css",
    }),
  ]),
};
