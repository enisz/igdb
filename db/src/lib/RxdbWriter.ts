import FileWriter from "../abstract/FileWriter";
import Document from "../model/Document";
import Fs from 'fs';
import { program } from 'commander';
import { RxCollection, RxDatabase, RxJsonSchema, addRxPlugin, createRxDatabase } from 'rxdb';
import { getRxStorageMemory } from 'rxdb/plugins/storage-memory';
import { RxDBJsonDumpPlugin } from 'rxdb/plugins/json-dump';
import { RxDBDevModePlugin } from 'rxdb/plugins/dev-mode';
import Md5 from 'md5';

export default class RxdbWriter extends FileWriter {
    public extension = 'json';

    public async write(document: Document): Promise<void> {
        let order = 1;
        addRxPlugin(RxDBJsonDumpPlugin);
        addRxPlugin(RxDBDevModePlugin);

        const rxdb: IGDBWrapperDatabase = await createRxDatabase<DocumentationDatabaseCollections>({
            name: 'igdbwdb',
            storage: getRxStorageMemory(),
        });

        const collections = await rxdb.addCollections({
            topics: {
                schema: this.getTopicJsonSchema(),
            },
            sections: {
                schema: this.getSectionJsonSchema()
            }
        });

        for (const topic of document.getTopics()) {
            const topicId = this.generateId(topic.getId(), topic.getSlug());

            await collections.topics.insert({
                id: topicId,
                order: order++,
                icon: topic.getIcon(),
                slug: topic.getSlug(),
                overview: topic.getOverview(),
                date: topic.getDate(),
                title: topic.getTitle(),
                body: topic.getBody(),
                stripped: topic.getStripped(),
                html: topic.getHtml(),
            });

            for (const section of topic.getSections()) {
                await collections.sections.insert({
                    id: this.generateId(section.getId(), section.getSlug()),
                    order: order++,
                    topicId,
                    parents: section.getParents().map(
                        (parentId: number) => {
                            const s: Section | undefined = topic.getSections().find((section: Section) => section.getId() === parentId);
                            return s ? this.generateId(s.getId(), s.getSlug()) : null;
                        }
                    ),
                    slug: section.getSlug(),
                    level: section.getLevel(),
                    title: section.getTitle(),
                    body: section.getBody(),
                    html: section.getHtml(),
                    stripped: section.getStripped()
                })
            }
        }

        Fs.writeFileSync(
            this.getAbsolutePath(),
            program.opts().production ? JSON.stringify(await rxdb.exportJSON()) : JSON.stringify(await rxdb.exportJSON(), null, 2),
            { encoding: 'utf-8' },
        );

        await rxdb.remove();
    }

    private generateId(id: number, slug: string): string {
        return Md5(`${id}|${slug}`).substring(0,10);
    }

    private getSectionJsonSchema(): RxJsonSchema<SectionDocumentType> {
        return {
          version: 0,
          primaryKey: 'id',
          type: 'object',
          properties: {
            id: {
              type: 'string',
              maxLength: 10,
            },
            order: {
              type: 'number',
            },
            slug: {
              type: 'string',
            },
            title: {
              type: 'string',
            },
            body: {
              type: 'string',
            },
            stripped: {
              type: 'string',
            },
            html: {
              type: 'string',
            },
            topicId: {
              type: 'string',
            },
            parents: {
              type: 'array',
              items: {
                type: 'number',
              }
            },
            level: {
              type: 'number',
            }
          }
        };
      }

      private getTopicJsonSchema(): RxJsonSchema<TopicDocumentType> {
        return {
          version: 0,
          primaryKey: 'id',
          type: 'object',
          properties: {
            id: {
              type: 'string',
              maxLength: 10,
            },
            order: {
              type: 'number',
            },
            icon: {
              type: 'string',
            },
            slug: {
              type: 'string',
            },
            overview: {
              type: 'string',
            },
            date: {
              type: ['number', 'null'],
            },
            title: {
              type: 'string',
            },
            body: {
              type: 'string',
            },
            stripped: {
              type: 'string',
            },
            html: {
              type: 'string',
            },
          }
        };
      }
}

export type TopicCollectionMethods = {
    count: () => Promise<number>;
}

export type TopicCollection = RxCollection<TopicDocumentType, TopicDocumentMethods, TopicCollectionMethods>;

import { RxDocument } from "rxdb";
import Section from "../model/Section";

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

export type SectionCollectionMethods = {
    count: () => Promise<number>;
}

export type SectionCollection = RxCollection<SectionDocumentType, SectionDocumentMethods, SectionCollectionMethods>;

export type SectionDocumentType = {
    id: string;
    order: number;
    topicId: string;
    parents: string[];
    slug: string;
    level: number;
    title: string;
    body: string;
    html: string;
    stripped: string;
}

export type SectionDocumentMethods = {};

export type SectionDocument = RxDocument<SectionDocumentType, SectionDocumentMethods>;

export type DocumentationDatabaseCollections = {
    topics: TopicCollection,
    sections: SectionCollection,
}

export type IGDBWrapperDatabase = RxDatabase<DocumentationDatabaseCollections>;
