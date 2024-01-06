import { getRxStorageMemory } from 'rxdb/plugins/storage-memory';
import { Injectable } from '@angular/core';
import { DocumentationDatabaseCollections, IGDBWrapperDatabase } from '../database/database';
import { RxJsonSchema, createRxDatabase, addRxPlugin, RxDumpDatabaseAny } from 'rxdb';
import { TopicDocumentType } from '../database/document/topic.document';
import { SectionDocumentType } from '../database/document/section.document';
import { RxDBJsonDumpPlugin } from 'rxdb/plugins/json-dump';
import { TopicCollection } from '../database/collection/topic.collection.';
import { SectionCollection } from '../database/collection/section.collection';

@Injectable({
  providedIn: 'root'
})
export class DatabaseService {
  private rxdb!: IGDBWrapperDatabase;

  public getTopicCollection(): TopicCollection {
    return this.rxdb.collections.topics
  }

  public getSectionCollection(): SectionCollection {
    return this.rxdb.collections.sections;
  }

  public async build(dump: RxDumpDatabaseAny<DocumentationDatabaseCollections>): Promise<void> {
    addRxPlugin(RxDBJsonDumpPlugin);
    this.rxdb = await createRxDatabase<DocumentationDatabaseCollections>({
      name: 'igdbwdb',
      storage: getRxStorageMemory(),
    });

    await this.rxdb.addCollections({
      topics: {
        schema: this.getTopicJsonSchema(),
      },
      sections: {
        schema: this.getSectionJsonSchema(),
      }
    });

    await this.rxdb.importJSON(dump);
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
