import { Injectable } from '@angular/core';
import { DatabaseService } from './database.service';
import { TopicDocumentMethods, TopicDocumentType } from '../database/document/topic.document';
import { MangoQuery, RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../database/document/section.document';

@Injectable({
  providedIn: 'root'
})
export class DocumentationService {
  constructor(
    private readonly databaseService: DatabaseService,
  ) {}

  public async getAllTopics(): Promise<RxDocument<TopicDocumentType, TopicDocumentMethods>[]> {
    const query: MangoQuery<TopicDocumentType> = {
      sort: [
        { order: 'asc' }
      ]
    };

    const collection = this.databaseService.getTopicCollection();
    const topics = await collection.find(query).exec();

    return topics;
  }

  public async getAllSections(): Promise<RxDocument<SectionDocumentType, SectionDocumentMethods>[]> {
    const query: MangoQuery<SectionDocumentType> = {
      sort: [
        { order: 'asc' }
      ]
    };

    const collection = await this.databaseService.getSectionCollection();
    const sections = await collection.find(query).exec();

    return sections;
  }

  public async getTopic(id: string): Promise<RxDocument<TopicDocumentType, TopicDocumentMethods>> {
    const query: MangoQuery<TopicDocumentType> = {
      selector: { id }
    };
    const collection = this.databaseService.getTopicCollection();
    const topic = await collection.findOne(query).exec() as RxDocument<TopicDocumentType, TopicDocumentMethods>;

    return topic;
  }

  public async getSection(id: string): Promise<RxDocument<SectionDocumentType, SectionDocumentMethods>> {
    const query: MangoQuery<SectionDocumentType> = {
      selector: { id }
    };

    const collection = await this.databaseService.getSectionCollection();
    const section = await collection.findOne(query).exec() as RxDocument<SectionDocumentType, SectionDocumentMethods>;
    return section;
  }

  public async getSections(ids: string[]): Promise<RxDocument<SectionDocumentType, SectionDocumentMethods>[]> {
    const query: MangoQuery<SectionDocumentType> = {
      selector: {
        id: {
          $in: ids
        }
      }
    };
    const collection = this.databaseService.getSectionCollection();
    const sections = await collection.find(query).exec();

    return sections;
  }

  public async getTopics(ids: string[]): Promise<RxDocument<TopicDocumentType, TopicDocumentMethods>[]> {
    const query: MangoQuery<TopicDocumentType> = {
      selector: {
        id: {
          $in: ids
        }
      },
      sort: [
        { order: 'asc' },
      ]
    };

    const collection = await this.databaseService.getTopicCollection();
    const topics = await collection.find(query).exec();

    return topics;
  }

  public async findSections(term: string): Promise<RxDocument<SectionDocumentType, SectionDocumentMethods>[]> {
    const searchRegexp = new RegExp(term, 'gi');
    const query: MangoQuery<SectionDocumentType> = {
      selector: {
        $or: [
          {
            stripped: {
              $regex: searchRegexp,
            }
          },
          {
            title: {
              $regex: searchRegexp,
            }
          }
        ]
      },
      sort: [
        { order: 'asc' },
      ],

    };
    const collection = await this.databaseService.getSectionCollection();
    const sections = await collection.find(query).exec();

    return sections;
  }

  public async getSectionsByTopic(topicId: string): Promise<RxDocument<SectionDocumentType, SectionDocumentMethods>[]> {
    const query: MangoQuery<SectionDocumentType> = {
      selector: {
        topicId
      },
      sort: [
        { order: 'asc' }
      ]
    };

    const collection = await this.databaseService.getSectionCollection();
    const sections = await collection.find(query).exec();

    return sections;
  }
}
