import { AfterViewInit, Component, HostListener, OnDestroy, OnInit } from '@angular/core';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { DocumentationService } from '../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { TopicDocumentMethods, TopicDocumentType } from '../../database/document/topic.document';
import { AsyncPipe, ViewportScroller } from '@angular/common';
import { TopicComponent } from './topic/topic.component';
import HighlightJS from 'highlight.js';
import { Subscription, take } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
import { NgbScrollSpy, NgbScrollSpyModule, NgbScrollSpyService } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-documentation',
  standalone: true,
  imports: [TopBarComponent, SidebarComponent, AsyncPipe, TopicComponent, NgbScrollSpyModule, NgbScrollSpy],
  templateUrl: './documentation.component.html',
  styleUrl: './documentation.component.scss'
})
export class DocumentationComponent implements OnInit, AfterViewInit, OnDestroy {
  private body: HTMLBodyElement;
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly viewportScroller: ViewportScroller,
    private readonly activatedRoute: ActivatedRoute,
    private readonly ngbScrollSpyService: NgbScrollSpyService,
  ) {
    this.body = document.getElementsByTagName('body')[0];
  }
  public async ngOnInit(): Promise<void> {
    this.body.classList.add('docs-page');
    this.topics = await this.documentationService.getAllTopics();
    this.subscriptions.push(
      // this.ngbScrollSpyService.active$.subscribe(
      //   (id: string) => {
      //     console.log('active changed');
      //     console.log('id: ' + id);
      //   }
      // )
    )

    // this.ngbScrollSpyService.active$.subscribe(
    //   (id: string) => {
    //     console.log('active changed');
    //     console.log('id: ' + id);
    //   }
    // )

    // console.log('start()');
    // this.ngbScrollSpyService.start();

    setTimeout(() => HighlightJS.highlightAll());
    this.viewportScroller.setOffset([0, 86]);
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

    this.ngbScrollSpyService.stop();
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }
}
