import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { DocumentationService } from '../../../service/documentation.service';
import { EmphasizePipe } from '../../../pipe/emphasize.pipe';
import { ActivatedRoute, Params, RouterLink } from '@angular/router';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-search-result-item',
  standalone: true,
  imports: [EmphasizePipe, RouterLink],
  templateUrl: './search-result-item.component.html',
  styleUrl: './search-result-item.component.scss'
})
export class SearchResultItemComponent implements OnInit, OnDestroy {
  @Input('section') public section!: RxDocument<SectionDocumentType, SectionDocumentMethods>;
  public emphasize = '';
  public parents: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];
  public topic!: RxDocument<TopicDocumentType, TopicDocumentMethods>;
  public loaded = false;
  public matches: string[] = [];
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly activatedRoute: ActivatedRoute,
  ) {}

  public async ngOnInit(): Promise<void> {
    this.subscriptions.push(
      this.activatedRoute.queryParams.subscribe(
        (params: Params) => {
          const { term } = params;

          if (term) {
            this.emphasize = term;
          }
        }
      )
    );

    this.topic = await this.documentationService.getTopic(this.section.topicId);
    this.parents = await this.documentationService.getSections(this.section.parents);
    this.matches = this.collectMatches();
    this.loaded = true;
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public getMap(): string[] {
    const titles = [
      this.topic.title
    ];

    for (const parent of this.parents) {
      titles.push(parent.title);
    }

    return titles;
  }

  private collectMatches(): string[] {
    const content = this.section.stripped;
    const emphasize = this.emphasize;
    const surroundingLength = 90;
    const output: string[] = [];

    const matches = content.matchAll(new RegExp(emphasize, 'gi'));

    console.log('-----')
    for (const match of matches) {
      console.log(match);
      const index = match.index as number;
      const nextSpaceIndex = this.section.stripped.indexOf(' ', index + surroundingLength);
      const previousSpaceIndex = this.section.stripped.lastIndexOf(' ', index - surroundingLength);
      const start = previousSpaceIndex === -1 ? 0 : previousSpaceIndex;
      const end = nextSpaceIndex === -1 ? this.section.stripped.length : nextSpaceIndex;
      const item: string[] = [];

      if (start > 0) item.push('...');
      item.push(this.section.stripped.substring(start, end));
      if (end < this.section.stripped.length) item.push('...');

      output.push(item.join(' '));
    }
    return output;
  }
}
