import { RxCollection } from "rxdb";
import { SectionDocumentType, SectionDocumentMethods } from "../document/section.document";

export type SectionCollectionMethods = {
    count: () => Promise<number>;
}

export type SectionCollection = RxCollection<SectionDocumentType, SectionDocumentMethods, SectionCollectionMethods>;
