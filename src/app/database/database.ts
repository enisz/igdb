import { RxDatabase } from "rxdb";
import { SectionCollection } from "./collection/section.collection";
import { TopicCollection } from "./collection/topic.collection.";

export type DocumentationDatabaseCollections = {
    topics: TopicCollection,
    sections: SectionCollection,
}

export type IGDBWrapperDatabase = RxDatabase<DocumentationDatabaseCollections>;
