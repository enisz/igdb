import { Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { PageHeaderComponent } from '../../component/page-header/page-header.component';
import { DocumentationService } from '../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { SectionDocumentType, SectionDocumentMethods } from '../../database/document/section.document';
import { ReactiveFormsModule } from '@angular/forms';
import { SearchResultItemComponent } from './search-result-item/search-result-item.component';

@Component({
  selector: 'app-search',
  standalone: true,
  imports: [TopBarComponent, PageHeaderComponent, ReactiveFormsModule, SearchResultItemComponent],
  templateUrl: './search.component.html',
  styleUrl: './search.component.scss'
})
export class SearchComponent implements OnInit, OnDestroy {
  public searchTerm = '';
  public loading = true;
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];
  private subscrptions: Subscription[] = [];

  public constructor(
    private readonly activatedRoute: ActivatedRoute,
    private readonly documentationService: DocumentationService,
    private readonly router: Router,
  ) { }

  public ngOnInit(): void {
    this.subscrptions.push(
      this.activatedRoute.queryParams.subscribe(
        (params: Params) => {
          const { term } = params;

          if(term) {
            this.searchTerm = term;
            this.find(term);
          } else {
            this.router.navigate(['']);
          }
        }
      )
    )
  }

  private async find(term: any): Promise<void> {
    this.loading = true;
    this.sections = await this.documentationService.findSections(term);
    this.loading = false;

  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscrptions) {
      subscription?.unsubscribe();
    }
  }
}
