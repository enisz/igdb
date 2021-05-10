import Lokijs from 'lokijs';
//import Datasource from '../assets/datasource/database.json';

let database = new Lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory"});
//database.loadJSONObject(Datasource);

export const loadDatabase = path => {
    return new Promise(
        (resolve, reject) => {
            fetch(path)
            .then(response => response.json())
            .then(data => {
                database = new Lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory"});
                database.loadJSONObject(data);
                resolve();
            })
            .catch(error => reject(error));
        }
    );
}

//export const getTopics = () => database.getCollection("templates").find({ parent : null });

//export const getParagraphsByParent = parent => database.getCollection("templates").find({ parent : parent });

export const getParagraphs = query => database.getCollection("templates").find(query);

//export const getTopic = topicId => database.getCollection("topics").findOne({ id : topicId });

//export const  = topicId => database.getCollection("paragraphs").find({ parent : topicId });

//export const searchParagraphs = term => database.getCollection("paragraphs").find({ "body.stripped" : { "$regex" : new RegExp(term, "g")}});

export const where = query => database.getCollection("templates").where(query);