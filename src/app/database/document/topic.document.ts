import { RxDocument } from "rxdb";

export type TopicDocumentType = {
    id: string;
    order: number;
    icon: string;
    slug: string;
    overview: string;
    date: number | null;
    title: string;
    body: string;
    stripped: string;
    html: string;
}

export type TopicDocumentMethods = {};

export type TopicDocument = RxDocument<TopicDocumentType, TopicDocumentMethods>;
