import { ViewportScroller } from '@angular/common';
import { AfterViewInit, Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import HighlightJS from 'highlight.js';
import { RxDocument } from 'rxdb';
import { Subscription, take } from 'rxjs';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { SectionDocumentMethods, SectionDocumentType } from '../../database/document/section.document';
import { TopicDocumentMethods, TopicDocumentType } from '../../database/document/topic.document';
import { DocumentationService } from '../../service/documentation.service';
import { SidebarComponent } from './sidebar/sidebar.component';
import { TopicComponent } from './topic/topic.component';

@Component({
  selector: 'app-documentation',
  standalone: true,
  imports: [TopBarComponent, SidebarComponent, TopicComponent],
  templateUrl: './documentation.component.html',
  styleUrl: './documentation.component.scss'
})
export class DocumentationComponent implements OnInit, AfterViewInit, OnDestroy {
  private body: HTMLBodyElement;
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];
  public fragments: string[] = [];
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly viewportScroller: ViewportScroller,
    private readonly activatedRoute: ActivatedRoute,
  ) {
    this.body = document.getElementsByTagName('body')[0];
  }
  public async ngOnInit(): Promise<void> {
    this.body.classList.add('docs-page');
    this.topics = await this.documentationService.getAllTopics();
    this.sections = await this.documentationService.getAllSections();

    this.fragments = [];
    this.fragments = this.fragments.concat(this.topics.map((topic: RxDocument<TopicDocumentType, TopicDocumentMethods>) => topic.slug));
    this.fragments = this.fragments.concat(this.sections.map((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.slug));

    setTimeout(() => HighlightJS.highlightAll());
    this.viewportScroller.setOffset([0, 70]);
  }

  public ngAfterViewInit(): void {
    this.activatedRoute.fragment.pipe(take(1)).subscribe(
      (fragment: string | null) => {
        if(fragment) {
          setTimeout(() => this.viewportScroller.scrollToAnchor(fragment), 100);
        }
      }
    )
  }

  public ngOnDestroy(): void {
    this.body.classList.remove('docs-page');

    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }
}
