import { RxCollection } from "rxdb";
import { SectionDocumentMethods, SectionDocumentType } from "../document/section.document";

export type SectionCollectionMethods = {
    count: () => Promise<number>;
}

export type SectionCollection = RxCollection<SectionDocumentType, SectionDocumentMethods, SectionCollectionMethods>;
