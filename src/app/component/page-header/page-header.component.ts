import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { NtkmeButtonComponent, NtkmeButtonModule } from '@ctrl/ngx-github-buttons';
import { ViewportService } from '../../service/viewport.service';
import { IViewportBreakpoint } from '../../interface/viewport.interface';
import { Subscription } from 'rxjs';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { SearchService } from '../../service/search.service';

@Component({
  selector: 'app-page-header',
  standalone: true,
  imports: [NtkmeButtonModule, ReactiveFormsModule],
  templateUrl: './page-header.component.html',
  styleUrl: './page-header.component.scss'
})
export class PageHeaderComponent implements OnInit, OnDestroy {
  @Input('title') public title = '';
  @Input('sub-title') public subTitle = '';
  @Input('show-github-buttons') public showGithubButtons = false;
  @Input('search-term') public searchTerm = '';
  private subscriptions: Subscription[] = [];
  public user = 'enisz';
  public repo = 'igdb';
  public count = true;
  public size: 'none' | 'large' = 'large';
  public types: ('star' | 'follow' | 'watch' | 'fork' | 'issue' | 'download')[] = ['follow', 'star', 'watch', 'fork'];
  public searchForm: FormGroup;

  public constructor(
    private readonly viewportService: ViewportService,
    private readonly router: Router,
    private readonly activatedRoute: ActivatedRoute,
    private readonly searchService: SearchService,
  ) {
    this.searchForm = new FormGroup({
      term: new FormControl('', [Validators.required]),
    });
  }

  public ngOnInit(): void {
    this.subscriptions.push(
      this.viewportService.getBreakpointObservable().subscribe(
        (breakpoint: IViewportBreakpoint) => this.size = breakpoint === 'xs' ? 'none' : 'large'
      )
    );

    if(this.searchTerm) {
      this.searchForm.get('term')?.setValue(this.searchTerm);
    }

    // this.subscriptions.push(
    //   this.activatedRoute.queryParams.subscribe(
    //     (params: Params) => {
    //       const { term } = params;

    //       if(term) {
    //         this.searchForm.get('term')?.setValue(term);
    //       }
    //     }
    //   )
    // )
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public openSearch(): void {
    this.searchService.setModalVisibility(true);
  }

  public handleSearch(event: SubmitEvent): void {
    event.preventDefault();
    const term = this.searchForm.get('term')?.value;
    this.router.navigate(['search'], { queryParams: { term }});
  }
}
