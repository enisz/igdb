const path = require("path");
const fs = require("fs");
const series = require("gulp").series;
const log = require("fancy-log");
const parallel = require("gulp").parallel;

const taskPath = path.join(__dirname, "gulp");
const task = {};

// loading tasks
fs.readdirSync(taskPath, { encoding : "utf-8"}).filter( file => !file.startsWith("_") && fs.lstatSync(path.join(taskPath, file)).isFile() ).forEach(
    file => {
        task[path.basename(file, ".js")] = require(path.join(taskPath, file));
    }
)