import { RxDocument } from "rxdb";

export type SectionDocumentType = {
    id: string;
    order: number;
    topicId: string;
    parents: string[];
    slug: string;
    level: number;
    title: string;
    html: string;
    stripped: string;
}

export type SectionDocumentMethods = {};

export type SectionDocument = RxDocument<SectionDocumentType, SectionDocumentMethods>;
