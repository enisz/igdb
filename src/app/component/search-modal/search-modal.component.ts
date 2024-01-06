import { ISearchResultSection } from './../../interface/search-modal.interface';
import { AfterViewInit, Component, HostListener, OnDestroy, OnInit, TemplateRef, ViewChild, ViewEncapsulation } from '@angular/core';
import { SearchService } from '../../service/search.service';
import { Subscription } from 'rxjs';
import { NgbModal, NgbModalOptions, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { DocumentationService } from '../../service/documentation.service';
import { NgTemplateOutlet } from '@angular/common';
import { ISearchResult } from '../../interface/search-modal.interface';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../database/document/section.document';
import { EmphasizePipe } from '../../pipe/emphasize.pipe';

@Component({
  selector: 'app-search-modal',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, EmphasizePipe],
  templateUrl: './search-modal.component.html',
  styleUrl: './search-modal.component.scss',
  encapsulation: ViewEncapsulation.None,
})
export class SearchModalComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('content') public content!: TemplateRef<NgTemplateOutlet>;
  public recentSearches: string[] = [];
  public results: ISearchResult[] = [];
  public modalRef: NgbModalRef | null = null;
  public searchForm: FormGroup;
  public searchCompactLength = 5;
  public showAllHistory = false;
  public selectedRow = -1;
  public sectionCount = 0;
  private subscriptions: Subscription[] = [];

  public constructor(
    private readonly searchService: SearchService,
    private readonly modalService: NgbModal,
    private readonly documentationService: DocumentationService,
    private readonly router: Router,
  ) {
    this.searchForm = new FormGroup({
      term: new FormControl('')
    });
  }

  public ngOnInit(): void {
    this.recentSearches = this.searchService.getRecentSearchTerms();
  }

  public ngAfterViewInit(): void {
    this.subscriptions.push(
      this.searchService.searchModalVisibleObservable().subscribe(
        (visible: boolean) => {
          if (visible) {
            const modalOptions: NgbModalOptions = {
              scrollable: true,
              keyboard: false,
              backdrop: 'static',
            };

            this.modalRef = this.modalService.open(this.content, modalOptions);
          } else {
            this.modalRef?.close();
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

  public async handleSearch(): Promise<void> {
    this.sectionCount = 0;
    this.selectedRow = -1;
    const { value: term } = this.searchForm.get('term') as FormControl;
    const sections = await this.documentationService.findSections(term);

    if(!sections.length) {
      this.results = [];
      return;
    }

    this.sectionCount = sections.length;

    const topics = await this.documentationService.getTopics(
      sections
        .map((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.topicId)
        .filter((id: string, index: number, array: string[]) => array.indexOf(id) === index)
    );

    this.results = [];
    let order = 0;
    for (const topic of topics) {
      this.results.push({
        id: topic.id,
        icon: topic.icon,
        title: topic.title,
        sections: sections
          .filter((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.topicId === topic.id)
          .map((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => ({ id: section.id, slug: section.slug, title: section.title, order: order++ }))
      })
    }
  }

  public removeHistoryItem(term?: string): void {
    if(term) {
      this.searchService.removeRecentSearchTerm(term);
    } else {
      this.searchService.clearRecentSearchTerms();
    }

    this.recentSearches = this.searchService.getRecentSearchTerms();
  }

  public setShowAllHistory(show: boolean): void {
    this.showAllHistory = show;
  }

  public searchRecent(term: string): void {
    this.searchForm.get('term')?.setValue(term);
    this.handleSearch();
  }

  public close(): void {
    if (this.modalService.hasOpenModals()) {
      this.searchService.setModalVisibility(false);
      const term = this.searchForm.get('term')?.value;

      if (term) {
        this.searchService.addRecentSearchTerm(term);
      }

      setTimeout(() => {
        this.results = [];
        this.searchForm.get('term')?.setValue('');
        this.recentSearches = this.searchService.getRecentSearchTerms();
      }, 500);
    }
  }

  @HostListener('document:keydown', ['$event'])
  private handleKeyboardEvents(event: KeyboardEvent): void {
    const { key, ctrlKey } = event;

    // open
    if (ctrlKey && key.toLowerCase() === 'k') {
      this.handleKeyK(event);
    }

    // close
    if (key.toLowerCase() === 'escape') {
      this.handleKeyEscape();
    }

    // up
    if (key.toLowerCase() === 'arrowup') {
      this.handleKeyArrowUp(event);
    }

    // down
    if (key.toLowerCase() === 'arrowdown') {
      this.handleKeyArrowDown(event);
    }

    // enter
    if (key.toLowerCase() === 'enter') {
      this.handleKeyEnter(event);
    }
  }

  private handleKeyK(event: KeyboardEvent): void {
    event.preventDefault();
    this.searchService.setModalVisibility(this.modalService.hasOpenModals() ? false : true);
  }

  private handleKeyEscape(): void {
    this.close();
  }

  private handleKeyArrowUp(event: KeyboardEvent): void {
    event.preventDefault();
    if (this.selectedRow > 0) {
      this.selectedRow--;
    }
  }

  private handleKeyArrowDown(event: KeyboardEvent): void {
    event.preventDefault();
    if (this.selectedRow < this.sectionCount - 1) {
      this.selectedRow++;
    }
  }

  private handleKeyEnter(event: KeyboardEvent): void {
    event.preventDefault();

    let selected: ISearchResultSection | null = null;

    for (const topic of this.results) {
      for (const section of topic.sections) {
        if (section.order === this.selectedRow) {
          selected = section;
          break;
        }
      }
    }

    if (selected) {
      this.router.navigate(['documentation'], { fragment: selected.slug });
      this.close();
    }
  }
}