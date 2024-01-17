import { AfterViewInit, Component, ElementRef, HostListener, OnDestroy, OnInit, TemplateRef, ViewChild, ViewEncapsulation } from '@angular/core';
import { SearchService } from '../../service/search.service';
import { Subscription } from 'rxjs';
import { NgbModal, NgbModalOptions, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { DocumentationService } from '../../service/documentation.service';
import { CommonModule, NgTemplateOutlet } from '@angular/common';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../database/document/section.document';
import { EmphasizePipe } from '../../pipe/emphasize.pipe';
import { IModalListGroup, IModalListGroupItem } from '../../interface/search-modal.interface';

@Component({
  selector: 'app-search-modal',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, EmphasizePipe, CommonModule],
  templateUrl: './search-modal.component.html',
  styleUrl: './search-modal.component.scss',
  encapsulation: ViewEncapsulation.None,
})
export class SearchModalComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('content') public content!: TemplateRef<NgTemplateOutlet>;
  public modalRef: NgbModalRef | null = null;
  public searchForm: FormGroup;
  public modalListGroup: IModalListGroup[] = [];
  public activeRow = -1;
  public groupItemCount = 0;
  public stepSize = 3;
  private subscriptions: Subscription[] = [];
  private removingHistoryItem = false;
  private visible = false;

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
    this.modalListGroup = this.processSearchHistory(this.searchService.getRecentSearchTerms());
  }

  public ngAfterViewInit(): void {
    this.subscriptions.push(
      this.searchService.searchModalVisibleObservable().subscribe(
        (visible: boolean) => {
          this.visible = visible;
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

  public handleListItemClick(group: IModalListGroupItem): void {
    if (this.removingHistoryItem) {
      this.removingHistoryItem = false;
    } else if (group.slug) {
      this.router.navigate(['/documentation'], { fragment: group.slug });
      this.close();
    } else {
      this.searchForm.get('term')?.setValue(group.title);
      this.handleSearch();
    }
  }

  public clearSearchField(): void {
    const term = this.searchForm.get('term')?.value;

    if (term.length) {
      this.searchService.addRecentSearchTerm(term);
    }
    this.searchForm.get('term')?.setValue('');
    this.handleSearch();
  }

  public removeHistoryItem(item: string): void {
    this.removingHistoryItem = true;
    this.searchService.removeRecentSearchTerm(item);
    this.modalListGroup = this.processSearchHistory(this.searchService.getRecentSearchTerms());
  }

  public async handleSearch(): Promise<void> {
    this.activeRow = -1;
    this.groupItemCount = 0;
    this.modalListGroup = [];
    const { value: term } = this.searchForm.get('term') as FormControl;

    if (!term.length) {
      this.modalListGroup = this.processSearchHistory(this.searchService.getRecentSearchTerms());
      return;
    }

    const sections = await this.documentationService.findSections(term);

    const topics = await this.documentationService.getTopics(
      sections
        .map((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.topicId)
        .filter((id: string, index: number, array: string[]) => array.indexOf(id) === index)
    );

    let idCounter = 0;
    for (const topic of topics) {
      const listGroup: IModalListGroup = {
        title: topic.title,
        icon: topic.icon,
        items: [],
      };

      const relatedSections = sections
      .filter((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => section.topicId === topic.id)
      .map((section: RxDocument<SectionDocumentType, SectionDocumentMethods>) => ({ slug: section.slug, title: section.title }));

      for (const section of relatedSections) {
        this.groupItemCount++;
        listGroup.items.push({
          id: idCounter++,
          title: section.title,
          slug: section.slug,
        })
      }

      this.modalListGroup.push(listGroup);
    }
  }

  public close(): void {
    if (this.modalService.hasOpenModals()) {
      this.searchService.setModalVisibility(false);
      const term = this.searchForm.get('term')?.value;

      if (term) {
        this.searchService.addRecentSearchTerm(term);
      }

      setTimeout(() => {
        this.searchForm.get('term')?.setValue('');
        this.modalListGroup = this.processSearchHistory(this.searchService.getRecentSearchTerms());
      }, 500);
    }
  }

  @HostListener('document:keydown', ['$event'])
  private handleKeyboardEvents(event: KeyboardEvent): void {
    if (!this.visible) return;
    const { key, ctrlKey } = event;

    // open
    if (ctrlKey && key.toLowerCase() === 'k') {
      this.handleKeyK(event);
    }

    // escape
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

    // home
    if (key.toLowerCase() === 'home') {
      this.handleKeyHome(event);
    }

    // end
    if (key.toLowerCase() === 'end') {
      this.handleKeyEnd(event);
    }

    // page down
    if (key.toLowerCase() === 'pagedown') {
      this.handleKeyPageDown();
    }

    // page up
    if (key.toLowerCase() === 'pageup') {
      this.handleKeyPageUp()
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
    if (this.activeRow === -1) {
      this.activeRow = this.groupItemCount;
    }

    if (this.activeRow === 0) {
      this.activeRow = this.groupItemCount;
    }

    if (this.activeRow > 0) {
      this.activeRow--;
    }

    this.scrollIfRequired();
  }

  private handleKeyArrowDown(event: KeyboardEvent): void {
    event.preventDefault();

    if (this.activeRow === this.groupItemCount - 1) {
      this.activeRow = -1;
    }

    if (this.activeRow < this.groupItemCount - 1) {
      this.activeRow++;
    }

    this.scrollIfRequired();
  }

  private handleKeyEnter(event: KeyboardEvent): void {
    event.preventDefault();

    let selected: IModalListGroupItem | undefined;

    for (const item of this.modalListGroup) {
      selected = item.items.find((groupItem: IModalListGroupItem) => groupItem.id === this.activeRow);

      if (selected) {
        break;
      }
    }

    if (selected) {
      this.handleListItemClick(selected);
    }
  }

  private handleKeyHome(event: KeyboardEvent): void {
    event.preventDefault();
    this.activeRow = 0;

    this.scrollIfRequired();
  }

  private handleKeyEnd(event: KeyboardEvent): void {
    event.preventDefault();
    this.activeRow = this.groupItemCount - 1;

    this.scrollIfRequired();
  }

  private handleKeyPageUp(): void {
    const newValue = this.activeRow - this.stepSize;
    this.activeRow = newValue < 0 ? 0 : newValue;
    this.scrollIfRequired();
  }

  private handleKeyPageDown(): void {
    const newValue = this.activeRow + this.stepSize;
    this.activeRow = newValue > this.groupItemCount - 1 ? this.groupItemCount - 1 : newValue;
    this.scrollIfRequired();
  }

  private scrollIfRequired(): void {
    const button = document.getElementById('button-' + this.activeRow);
    const modal = document.getElementById('modal-body');

    if (button && modal) {
      const { top: modalTop, bottom: modalBottom } = modal.getBoundingClientRect();
      const { top: buttonTop, bottom: buttonBottom } = button.getBoundingClientRect();
      const scrollTop = modal.scrollTop;

      if (this.activeRow === 0) {
        modal.scrollTo({ behavior: 'instant', top: 0 });
      } else if (buttonBottom > modalBottom) {
        modal.scrollTo({ behavior: 'instant', top: scrollTop + (buttonBottom - modalBottom) + 10 });
      } else if (buttonTop < modalTop) {
        modal.scrollTo({ behavior: 'instant', top: scrollTop - (modalTop - buttonTop) - 10 });
      }
    }
  }

  private processSearchHistory(history: string[]): IModalListGroup[] {
    this.activeRow = -1;
    this.groupItemCount = 0;
    if (!history.length) {
      return [];
    }

    const listGroup: IModalListGroup = {
      title: 'Search History',
      icon: 'fa-clock-rotate-left',
      items: [],
    }

    let idCounter = 0;
    for (const item of history) {
      this.groupItemCount++;
      listGroup.items.push({
        id: idCounter++,
        title: item,
        clearable: true,
      })
    }

    return [listGroup];
  }
}
