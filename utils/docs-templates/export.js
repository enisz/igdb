const fs = require("fs");
const md2json = require("markdown-to-json");
const path = require("path");
const jsonmark = require("jsonmark");
const lokijs = require("lokijs");
const remark = require("remark");
const remarkExternalLinks = require("remark-external-links");
const remarkHtml = require("remark-html");
const remarkStrip = require("strip-markdown");

//const database = new lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory", serializationMethod : "normal" });
const database = new lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory", serializationMethod : "pretty" });
const topics = database.addCollection("topics");
const paragraphs = database.addCollection("paragraphs");

const generateId = (title) => {
    return title.toLowerCase().replace(new RegExp("( |,|\\.|'|!|\\?)", "g"), "-");
}

const documents = JSON.parse(
    md2json.parse(
        fs.readdirSync("templates").filter(file => file.endsWith(".md")).map(file => path.join("templates", file)),
        {
            width: 0,
            content: true
        }
    )
)

for(let key in documents) {
    const current = documents[key];
    const id = generateId(current.basename.substring(3));

    topics.insert({
        id : id,
        title : current.title,
        icon : current.icon,
        overview : current.overview,
        color : current.color ? current.color : null
    });

    const contents = jsonmark.parse(current.content.trim());

    for(let index in contents.content) {
        const head = contents.content[index].head;
        const body = contents.content[index].body;
        const level = head.match(new RegExp("#", "g")).length;
        const title = head.replace(new RegExp("#", "g"), "").trim();

        paragraphs.insert({
            id : generateId(title),
            title : title,
            level : level,
            parent : id,
            body : {
                stripped : remark().use(remarkStrip).processSync(body).contents.trim(),
                markdown : body,
                html : remark().use(remarkExternalLinks, {target : "_blank", rel : "nofollow"}).use(remarkHtml).processSync(body).contents.trim()
            }
        });

        //paragraphs.insert({
        //    id : generateId(title),
        //    title : title,
        //    level : level,
        //    parent : id,
        //    body : body
        //});
    }
}

fs.writeFileSync("database.json", database.serialize(), { encoding : "utf-8"});