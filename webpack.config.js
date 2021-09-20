const HtmlWebpackPlugin = require("html-webpack-plugin"); 

module.exports = {
  mode: "development",
  entry: "./plugins/arDominionB5Plugin/js/main.js",
  output: {
    path: __dirname + "/plugins/arDominionB5Plugin/build",
    filename: "js/bundle.[contenthash].js",
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: "./plugins/arDominionB5Plugin/templates/_layout_start_webpack.php",
      filename: "../templates/_layout_start.php",
      publicPath: "/plugins/arDominionB5Plugin/build",
      inject: false,
    })
  ]
};
