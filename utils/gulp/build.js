/**
 * Building the frontend application
 */

 const path = require("path");
 const spawn = require("cross-spawn");
 const dependency = require(path.join(__dirname, "_dependency.js"));
 const log = require("fancy-log");

 module.exports = Object.assign(
     (cb) => {
         const webappPath = "docs-app";

         dependency(webappPath)
             .then(
                 () => {
                     log.info(webappPath)
                     const process = spawn("ng", ["build", "--prod", "--base-href", "."], { cwd : webappPath });
                     const logOutput = data => data.toString().split("\n").filter(line => line.trim().length > 0).map( line => log.info(line.trim()));

                     process.stdout.on("data", logOutput)
                     process.stderr.on("data", logOutput)

                     process.on("exit", code => {
                         if(code != 0) {
                             cb("Process failed! Error code: " + code);
                         } else {
                             cb();
                         }
                     })
                 }
             )
             .catch(
                 error => cb(error)
             )
     }, { displayName : path.basename(__filename, ".js")}
 );