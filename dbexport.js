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
const IMAGE_REGEX = new RegExp("!\\[(.*?)\\]\\((.*?)\\)", "gi");

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
    console.log(`Exporting ${commander.opts().production ? "production " : ""}database`);
    const imagesPath = path.join(PUBLIC_PATH, "images");
    const database = new lokijs("DocumentationDB", { env : "BROWSER", persistenceMethod : "memory", serializationMethod : commander.opts().production ? "normal" : "pretty" });
    const templates = database.addCollection("templates");

    if(fs.existsSync(imagesPath)) {
        console.log(`\nClearing existing images in ${imagesPath}`);
        fs.rmSync(imagesPath, { recursive: true, force: true })
    }

    console.log(`\nProcessing templates in ${TEMPLATE_PATH}`);

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
        console.log(`\nTemplate: ${path.join(TEMPLATE_PATH, json[index].basename)}.md`);

        const current = json[index];
        const overview = current.overview;
        const icon = current.icon;
        const basename = current.basename;
        const paragraphs = jsonmark.parse(current.content.trim()).content;

        console.log(` Processing paragraphs in template:`)
        for(let title in paragraphs) {
            const paragraph = paragraphs[title];
            const level = paragraph.head.match(new RegExp("#", "g")).length;
            let slug;
            let counter = 1;

            console.log(` ${" ".repeat(level)}[${title}]`);

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
            const images = (paragraph.body.trim().match(IMAGE_REGEX) || []).length;

            if(images) {
                console.log(`  ${" ".repeat(level)}Found ${images} image${images > 1 ? "s" : ""}:`);
                let match;

                // while there are images in the template
                while(match = IMAGE_REGEX.exec(paragraph.body.trim())) {
                    const source = path.join(TEMPLATE_PATH, match[2]);
                    const target = path.join(PUBLIC_PATH, match[2]);
                    const targetDir = path.parse(target).dir;

                    // if the target path does not exist, create it
                    if(!fs.existsSync(targetDir)) {
                        fs.mkdirSync(targetDir, { recursive: true });
                    }
    
                    fs.copyFileSync(source, target);
                    console.log(`   ${" ".repeat(level)}- ${source} => ${target}`);
                }
            }            

            if(level == 1) {
                const time = execSync(`git log --format=%ct "${path.join(TEMPLATE_PATH, basename + ".md")}"`).toString().split("\n")[0].trim();
                toPush.icon = icon;
                toPush.overview = overview;
                toPush.timestamp = time.length > 0 ? parseInt(time)*1000 : null;
            }

            documents.push(toPush)
        }
    }

    console.log("\nMapping parents, assigning id's");
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
        documents[index].id = md5(documents[index].parents.length == 0 ? documents[index].title : documents[index].parents.concat([documents[index].title]).join("|")).substring(2,9);
    })

    console.log(`\nInjecting paragraphs to database:`)

    documents.forEach(item => {
        console.log(` [${item.id}]${" ".repeat(item.level)}${item.title}`);
        templates.insert(item)
    });

    console.log("\nSaving database");

    fs.writeFileSync(path.join(PUBLIC_PATH, "database.json"), database.serialize(), { encoding : "utf-8"});

    console.log(`\n${commander.opts().production ? "Production " : ""}Database saved: ${path.join(PUBLIC_PATH, "database.json")} (${calculateFileSize(fs.lstatSync(path.join(PUBLIC_PATH, "database.json")).size)})`);
}

// Exporting the database
exportDb();

// Watching for changes if -w or --watch is passed
if(commander.opts().watch) {
    console.log(`\nWatching for changes in ${TEMPLATE_PATH}`);
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