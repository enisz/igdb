import { AfterViewInit, Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { ViewportService } from '../../../service/viewport.service';
import { Subscription } from 'rxjs';
import { IViewportDimension } from '../../../interface/viewport.interface';
import { RxDocument } from 'rxdb';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { DocumentationService } from '../../../service/documentation.service';
import { RouterLink } from '@angular/router';
import { SearchFieldDirective } from '../../../directive/search-field.directive';
import { ScrollSpyService } from '../../../service/scroll-spy.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [RouterLink, SearchFieldDirective, CommonModule],
  templateUrl: './sidebar.component.html',
  styleUrl: './sidebar.component.scss'
})
export class SidebarComponent implements OnInit, AfterViewInit, OnDestroy {
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];
  private subscriptions: Subscription[] = [];
  @ViewChild('sidebar') private sidebar!: ElementRef;
  public activeIds: string[] = [];

  public constructor(
    private readonly viewportService: ViewportService,
    private readonly documentationService: DocumentationService,
    private readonly scrollSpyService: ScrollSpyService,
  ) {}

  public async ngOnInit(): Promise<void> {
    this.topics = await this.documentationService.getAllTopics();
    this.sections = await this.documentationService.getAllSections();
  }

  public ngAfterViewInit(): void {
    this.subscriptions.push(
      this.viewportService.getViewportObserable().subscribe(
        (dimension: IViewportDimension) => {
          if (dimension.width >= 1200) {
            this.showSidebar();
          } else {
            this.hideSidebar();
          }
        }
      )
    );

    this.subscriptions.push(
      this.scrollSpyService.getActiveObservable().subscribe(
        (fragments: string[]) => this.activeIds = fragments
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

  public closeSidebar(): void {
    if (this.viewportService.getViewportValue().width <= 1200) {
      this.hideSidebar();
    }
  }

  private showSidebar(): void {
    this.sidebar.nativeElement.classList.add('sidebar-visible');
    this.sidebar.nativeElement.classList.remove('sidebar-hidden');
  }

  private hideSidebar(): void {
    this.sidebar.nativeElement.classList.remove('sidebar-visible');
    this.sidebar.nativeElement.classList.add('sidebar-hidden');
  }
}