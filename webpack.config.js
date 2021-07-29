/* global require */
/* global module */
/* global process */
/* global __dirname */

let path = require('path')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const WebpackBar = require('webpackbar')
const Dotenv = require('dotenv-webpack')

module.exports = {
  mode: process.env.npm_lifecycle_event === 'production' ? 'production' : 'development',
  context: path.resolve(__dirname),
  devtool: process.env.npm_lifecycle_event === 'production' ? false : 'source-map',
  optimization: {
    splitChunks: {
      chunks: 'async',
      minSize: 30000,
      maxSize: 0,
      minChunks: 1,
      maxAsyncRequests: 5,
      maxInitialRequests: 3,
      automaticNameDelimiter: '~',
      name: true,
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]/,
          priority: -10
        },
        default: {
          minChunks: 2,
          priority: -20,
          reuseExistingChunk: true
        }
      }
    }
  },
  entry: {
    app: './src/js/app.js',
    styles: [
      './src/scss/styles.scss',
    ],
  },
  resolve: {
    alias: {
      vue: process.env.npm_lifecycle_event === 'production' ? 'vue/dist/vue.min.js' : 'vue/dist/vue.js'
    }
  },
  output: {
    publicPath: "/js/",
    path: path.resolve(__dirname, 'public/js'),
    filename: '[name].min.js',
    chunkFilename: '[name].bundle.js'
  },
  module: {
    rules: [
      {
        test: /\.scss|css$/,
        use: [
          {
            loader: 'style-loader' // creates style nodes from JS strings
          },
          {
            loader: 'css-loader' // translates CSS into CommonJS
          },
          {
            loader: 'sass-loader' // compiles Sass to CSS
          }
        ]
      },
      {
        test: /\.styl$/,
        loader: ['style-loader', 'css-loader', 'stylus-loader']
      },
      {
        test: /\.vue$/,
        use: [
          {
            loader: 'vue-loader'
          },
          {
            loader: "eslint-loader",
          }
        ]
      },
      {
        test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'file-loader',
          options: {
            name: '[name].[ext]',
            outputPath: '../font/'
          }
        }]
      },
      {
        test: /\.(png|jpg|gif|jpeg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'file-loader',
          options: {
            name: '[name].[ext]',
            outputPath: '../img/'
          }
        }]
      },
      {
        test: /\.(js)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          },
          {
            loader: "eslint-loader",
          }
        ]
      }
    ]
  },
  plugins: [
    new VueLoaderPlugin(),
    new WebpackBar(),
    new Dotenv()
  ]
}
