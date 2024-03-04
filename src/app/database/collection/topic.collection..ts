import { RxCollection } from "rxdb";
import { TopicDocumentMethods, TopicDocumentType } from "../document/topic.document";

export type TopicCollectionMethods = {
    count: () => Promise<number>;
}

export type TopicCollection = RxCollection<TopicDocumentType, TopicDocumentMethods, TopicCollectionMethods>;
