const romanize = require("romanize");
const md5 = require("md5");
const fs = require("fs");
const md2json = require("markdown-to-json");
const path = require("path");
const jsonmark = require("jsonmark");
const lokijs = require("lokijs");
const remark = require("remark");
const remarkExternalLinks = require("remark-external-links");
const remarkHtml = require("remark-html");
const remarkStrip = require("strip-markdown");
const dateParser = require("node-date-parser");
const execSync = require("child_process").execSync;
const remarkGfm = require("remark-gfm");
const commander = require("commander");

// Command Line Arguments
commander
    .option("-p, --production", "Build production database (without any whitespaces")
    .option("-w, --watch", "Watching file changes in the template folder")
    .parse(process.argv);

// Variables
const TEMPLATE_PATH = path.join(__dirname, "src", "assets", "templates");
const PUBLIC_PATH = path.join(__dirname, "public");

// Calculating file size
const calculateFileSize = size => {
    const units = ["kilobyte", "megabyte", "gigabyte"];

    if(size < 1024) {
        return `${size} byte${size > 1 ? "s" : ""}`;
    } else {
        for(let i=0; i<units.length; i++) {
            const result = size / (1024**(i+1));

            if(result < 1) {
                const actualSize = size / (1024**i);
                return `${parseFloat(actualSize).toFixed(2)} ${units[i-1]}${actualSize > 1 ? "s" : ""}`;
            }
        }
    }

    return `${size} byte${size > 1 ? "s" : ""}`;
}

// Exporting the database
const exportDb = () => {
    if(fs.existsSync(path.join(PUBLIC_PATH, "images"))) {
        console.log("Clearing existing images in public folder...");
        const deleteFiles = folder => {
            fs.readdirSync(folder, { encoding : "utf-8"}).forEach( item => {
                const currentItem = path.join(folder, item);
                const isFile = fs.lstatSync(currentItem).isFile();

                if(isFile) {
                    console.log(` Deleting ${currentItem}...`)
                    fs.rmSync(currentItem);
                } else {
                    console.log(` ${currentItem} is a folder. Going deeper...`);
                    deleteFiles(currentItem);

                    console.log(` ${currentItem} cleared! Deleting...`);
                    fs.rmdirSync(currentItem);
                }
            })
        }

        deleteFiles(path.join(PUBLIC_PATH, "images"));
    }
    console.log(`Exporting ${commander.opts().production ? "production " : " "}database...`);
    const database = new lokijs("DocumentationDB", { env : "BROWSER", persistenceMethod : "memory", serializationMethod : commander.opts().production ? "normal" : "pretty" });
    const templates = database.addCollection("templates");

    let documents = [];
    const json = JSON.parse(
        md2json.parse(
            fs.readdirSync(TEMPLATE_PATH).filter(file => file.endsWith(".md")).map(file => path.join(TEMPLATE_PATH, file)),
            {
                width: 0,
                content: true
            }
        )
    )

    for(let index in json) {
        console.log(`Processing template: ${json[index].basename}.md`);

        const current = json[index];
        const overview = current.overview;
        const icon = current.icon;
        const basename = current.basename;
        const paragraphs = jsonmark.parse(current.content.trim()).content;

        for(let title in paragraphs) {
            console.log(`Processing paragrah: ${title}`);

            const paragraph = paragraphs[title];
            const level = paragraph.head.match(new RegExp("#", "g")).length;
            let slug;
            let counter = 1;

            do {
                slug = (title + (counter > 1 ? `-${romanize(counter)}` : "")).trim()  .toLowerCase().replace(new RegExp("( |,|\\.|'|!|\\?|\\)|\\(|\\]|\\[|\\}|\\{)", "g"), "-");
                counter++;
            } while(documents.find( paragraph => paragraph.slug == slug) != undefined)

            let toPush = {
                id : "", // will be calculated later
                slug : slug,
                level : level,
                parent: null,
                parents : [],
                title : title.trim(),
                date : null,
                timestamp : null,
                body : {
                    stripped : remark().use(remarkStrip).processSync(paragraph.body.trim()).contents.trim(),
                    markdown : paragraph.body.trim(),
                    html : remark()
                        .use(remarkExternalLinks, {target : "_blank", rel : "nofollow"})
                        .use(remarkHtml)
                        .use(remarkGfm) // processing tables
                        .processSync(paragraph.body.trim())
                        .contents
                        .trim()
                        .replace(new RegExp("<(\/|)t(r|d|h|head|body|able)>\\n<(\/|)t(r|d|h|head|body|able)>", "gm"), "<$1t$2><$3t$4>") // removing newlines from tables for react
                        .replace(new RegExp("<(\/|)t(r|d|h|head|body|able)>\\n<(\/|)t(r|d|h|head|body|able)>", "gm"), "<$1t$2><$3t$4>")
                }
            }

            // processing images
            const match = new RegExp("!\\[(.*?)\\]\\((.*?)\\)", "gi").exec(paragraph.body.trim());
            if(match != null) {
                console.log(` Processing images in ${title}`);
                const source = match[2].split("/").join(path.sep);
                const target = path.join("public", source.split("/").join(path.sep));

                if(!fs.existsSync(target)) {
                    const dirtree = path.dirname(target).split(path.sep);
                    let targetPath = "";

                    for(let index in dirtree) {
                        if(targetPath == "") {
                            targetPath = dirtree[0];
                        } else {
                            targetPath = path.join(targetPath, dirtree[index]);
                        }

                        if(!fs.existsSync(targetPath)) {
                            fs.mkdirSync(targetPath)
                        }
                    }
                }

                fs.copyFileSync(path.join(TEMPLATE_PATH, source), target);
                console.log(`  Image ${path.basename(source)} from ${path.dirname(path.join(__dirname, source))} to ${path.dirname(path.join(__dirname, target))} copied!`);
            }

            if(level == 1) {
                const time = execSync(`git log --format="%cD|%ct" "${path.join(TEMPLATE_PATH, basename + ".md")}"`).toString().split("\n")[0].trim();
                toPush.icon = icon;
                toPush.overview = overview;
                if(time.length > 0) {
                    const [gitDate, gitTimestamp] = time.split("|");
                    toPush.date = `${dateParser.parse('jo', new Date(gitDate))} of ${dateParser.parse('F, Y', new Date(gitDate))}`;
                    toPush.timestamp = parseInt(gitTimestamp)*1000;
                } else {
                    [toPush.date, toPush.timestamp] = ["Not published yet", null]
                }
            }

            documents.push(toPush)
        }
    }

    console.log("Mapping parents, assigning id's...");
    documents.map( (paragraph, index) => {
        if(paragraph.level > 1) {
            let parents = [];
            let element = documents[index];
            let level = element.level;

            for(let i=index-1; i>=0; i--) {
                if(level == 1) {
                    break;
                } else if(documents[i].level == level - 1) {
                    parents.push(documents[i].id)
                    level = documents[i].level;
                }
            }

            documents[index].parents = parents.reverse();
            documents[index].parent = parents[0];
        }

        // assign id
        documents[index].id = md5(documents[index].parents.length == 0 ? documents[index].title : documents[index].parents.concat([documents[index].title]).join("|")).substr(2,9);
    })

    documents.forEach(item => {
        console.log(`Adding paragraph to the database: [${item.id}]${" ".repeat(item.level)}${item.title}`);
        templates.insert(item)
    });

    console.log("Saving database...");
    fs.writeFileSync(path.join(PUBLIC_PATH, "database.json"), database.serialize(), { encoding : "utf-8"});

    console.log(`${commander.opts().production ? "Production " : ""}Database saved: ${path.join(PUBLIC_PATH, "database.json")}`);
    const stats = fs.lstatSync(path.join(PUBLIC_PATH, "database.json"));

    console.log("\nDetails:");
    console.log("  File: " + path.join(PUBLIC_PATH, "database.json"));
    console.log("  Size: " + calculateFileSize(stats.size) + "\n");
}

// Exporting the database
exportDb();

// Watching for changes if -w or --watch is passed
if(commander.opts().watch) {
    console.log(`Watching changes in ${TEMPLATE_PATH}...`);
    let fsWait = false;
    fs.watch(TEMPLATE_PATH, (event, filename) => {
        if (filename) {
            if (fsWait) return;
            fsWait = setTimeout(() => {
            fsWait = false;
            }, 100);
            console.log(`${path.join(TEMPLATE_PATH, filename)} ${event}d!`);
            exportDb();
        }
    });
}