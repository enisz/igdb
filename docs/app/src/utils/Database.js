import Lokijs from 'lokijs';
import Datasource from '../assets/datasource/database.json';

let database = new Lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory"});
database.loadJSONObject(Datasource);

//export const loadDatabase = path => {
//    return new Promise(
//        (resolve, reject) => {
//            fetch(path)
//            .then(response => response.json())
//            .then(data => {
//                database = new Lokijs("WrapperDocsDB", { env : "BROWSER", persistenceMethod : "memory"});
//                database.loadJSONObject(data);
//                resolve();
//            })
//            .catch(error => reject(error));
//        }
//    );
//}

export const getTopics = () => database.getCollection("topics").find({});

export const getTopic = topicId => database.getCollection("topics").findOne({ id : topicId });

export const getParagraphs = topicId => database.getCollection("paragraphs").find({ parent : topicId });