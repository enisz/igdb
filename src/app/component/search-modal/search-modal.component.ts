import { AfterViewInit, Component, HostListener, OnDestroy, OnInit, TemplateRef, ViewChild, ViewEncapsulation } from '@angular/core';
import { RouterLink } from '@angular/router';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { DocumentationService } from '../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../database/document/section.document';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { BehaviorSubject, Subscription, debounceTime, distinctUntilChanged, filter, take } from 'rxjs';
import { TopicDocumentMethods, TopicDocumentType } from '../../database/document/topic.document';
import { ISearchResult } from '../../interface/search-modal.interface';
import { SearchService } from '../../service/search.service';

@Component({
  selector: 'app-search-modal',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule],
  templateUrl: './search-modal.component.html',
  styleUrl: './search-modal.component.scss',
  encapsulation: ViewEncapsulation.None,
})
export class SearchModalComponent implements AfterViewInit, OnDestroy {
  @ViewChild('content') public content!: TemplateRef<any>;
  public modalRef!: NgbModalRef;
  public loading = false;
  public results: ISearchResult[] = [];
  public searchForm: FormGroup;
  private searchSubject: BehaviorSubject<string>;
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly ngbModalService: NgbModal,
    private readonly searchService: SearchService,
    private readonly documentationService: DocumentationService,
  ) {
    this.searchForm = new FormGroup({
      term: new FormControl('')
    });

    this.searchSubject = new BehaviorSubject('');
  }

  public ngAfterViewInit(): void {
    this.subscriptions.push(
      this.searchSubject.pipe(
        filter(x => !!x),
        debounceTime(300),
        distinctUntilChanged(),
      ).subscribe((term: string) => this.find(term))
    );

    this.subscriptions.push(
      this.searchService.searchModalVisibleObservable().subscribe(
        (visible: boolean) => {
          if (visible) {
            this.modalRef = this.ngbModalService.open(this.content, { scrollable: true });
          } else {
            this.closeModal();
          }
        }
      )
    );

    // this.subscriptions.push(
    //   this.modalRef?.hidden.subscribe(
    //     () => {
    //       console.log('hidden subscription');
    //       this.cleanup();
    //     }
    //   )
    // )
  }

  private cleanup(): void {
    console.log('cleanup');
    this.results = [];
    this.searchService.addRecentSearchTerm(this.searchForm.get('term')?.value)
    this.searchForm.get('term')?.setValue('');
  }

  public closeModal(): void {
    this.modalRef?.hidden.pipe(take(1)).subscribe(() => this.cleanup());
    const term = this.searchForm.get('term')?.value || null;
    if (term) {
      this.searchService.addRecentSearchTerm(term);
    }
    this.modalRef?.close();
  }

  private async find(term: string): Promise<void> {
    console.log('find!');
    this.loading = true;
    const sections = await this.documentationService.findSections(term);
    const topicIds = sections.map((section) => section.topicId).filter((topicId, index, array) => array.indexOf(topicId) === index);
    const topics = await this.documentationService.getTopics(topicIds);

    this.results = [];

    for (const topic of topics) {
      this.results.push({
        id: topic.id,
        icon: topic.icon,
        title: topic.title,
        sections: sections.filter(section => section.topicId === topic.id).map(section => ({ id: section.id, slug: section.slug, title: section.title }))
      })
    }
    console.log(this.results);
    this.loading = false;
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public handleSearch(event: SubmitEvent): void {
    event.preventDefault();
  }

  public handleInput(event: Event): void {
    this.searchSubject.next((event.currentTarget as HTMLInputElement).value)
  }

  @HostListener('document:keydown', ['$event'])
  private handleSearchModal(event: KeyboardEvent): void {
    const { key, ctrlKey } = event;
    if (ctrlKey && key === 'k') {
      event.preventDefault();
      this.searchService.setModalVisibility(this.ngbModalService.hasOpenModals() ? false : true);
    }
  }
}
