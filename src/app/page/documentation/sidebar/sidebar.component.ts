import { AfterViewInit, Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { ViewportService } from '../../../service/viewport.service';
import { Subscription } from 'rxjs';
import { IViewportDimension } from '../../../interface/viewport.interface';
import { RxDocument } from 'rxdb';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { DocumentationService } from '../../../service/documentation.service';
import { RouterLink } from '@angular/router';
import { NgbScrollSpyModule, NgbScrollSpyService } from '@ng-bootstrap/ng-bootstrap';
import { AsyncPipe } from '@angular/common';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [RouterLink, NgbScrollSpyModule, AsyncPipe],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent implements OnInit, AfterViewInit, OnDestroy {
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];
  private subscriptions: Subscription[] = [];
  @ViewChild('sidebar') private sidebar!: ElementRef<HTMLDivElement>;
  public activeId = '';


  public constructor(
    private readonly viewportService: ViewportService,
    private readonly documentationService: DocumentationService,
    private readonly ngbScrollSpyService: NgbScrollSpyService,
  ) {}

  public async ngOnInit(): Promise<void> {
    this.topics = await this.documentationService.getAllTopics();
    this.sections = await this.documentationService.getAllSections();

    this.subscriptions.push(
      this.ngbScrollSpyService.active$.subscribe(
        (id: string) => {
          console.log('active$: ' + id);
          this.activeId = id;
        }
      )
    )
  }

  public ngAfterViewInit(): void {
    this.subscriptions.push(
      this.viewportService.getViewportObserable().subscribe(
        (dimension: IViewportDimension) => {
          if (dimension.width >= 1200) {
            this.sidebar.nativeElement.classList.add('sidebar-visible');
            this.sidebar.nativeElement.classList.remove('sidebar-hidden');
          } else {
            this.sidebar.nativeElement.classList.remove('sidebar-visible');
            this.sidebar.nativeElement.classList.add('sidebar-hidden');
          }
        }
      )
    );
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public filterByTopic(topicId: string): RxDocument<SectionDocumentType, SectionDocumentMethods>[] {
    return this.sections.filter((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.topicId === topicId);
  }
}
