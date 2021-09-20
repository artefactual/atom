const HtmlWebpackPlugin = require("html-webpack-plugin");

module.exports = {
  mode: "development",
  entry: "./plugins/arDominionB5Plugin/webpack.entry.js",
  output: {
    path: __dirname + "/plugins/arDominionB5Plugin/build",
    filename: "js/bundle.[contenthash].js",
  },
  module: {
    rules: [
      {
        test: /\.(sa|sc|c)ss$/i,
        use: [
          "style-loader",
          "css-loader",
          "resolve-url-loader",
          {
            loader: "sass-loader",
            options: { sourceMap: true },
          },
        ],
      },
    ],
  },
  plugins: [
    new HtmlWebpackPlugin({
      template:
        "./plugins/arDominionB5Plugin/templates/_layout_start_webpack.php",
      filename: "../templates/_layout_start.php",
      publicPath: "/plugins/arDominionB5Plugin/build",
      inject: false,
    }),
  ],
};
