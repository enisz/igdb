import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { RxDocument } from 'rxdb';
import { TopicDocumentType, TopicDocumentMethods } from '../../database/document/topic.document';
import { PageFooterComponent } from '../../component/page-footer/page-footer.component';
import { RouterLink } from '@angular/router';
import { DocumentationService } from '../../service/documentation.service';
import { ICommits } from '../../interface/git.interface';
import { GitService } from '../../service/git.service';
import { CommonModule, ViewportScroller } from '@angular/common';
import { ViewportService } from '../../service/viewport.service';
import { IViewportBreakpoint } from '../../interface/viewport.interface';
import { Subscription } from 'rxjs';
import { NtkmeButtonModule } from '@ctrl/ngx-github-buttons';
import { SearchFieldDirective } from '../../directive/search-field.directive';
import { NgbCollapse } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { IToken } from '../../interface/token.interface';
import { ToastrService } from 'ngx-toastr';
import { TokenService } from '../../service/token.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [TopBarComponent, PageFooterComponent, RouterLink, CommonModule, NtkmeButtonModule, SearchFieldDirective, NgbCollapse, ReactiveFormsModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit, OnDestroy {
  @ViewChild('collapse') private collapse!: ElementRef<HTMLDivElement>;
  public isCollapsed = true;
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public latestCommits!: Promise<ICommits[]>;
  public user = 'enisz';
  public repo = 'igdb';
  public count = true;
  public size: 'none' | 'large' = 'large';
  public types: ('star' | 'follow' | 'watch' | 'fork' | 'issue' | 'download')[] = ['follow', 'star', 'watch'];
  public tokenForm: FormGroup;
  private subscriptions: Subscription[] = [];
  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly gitService: GitService,
    private readonly viewportService: ViewportService,
    private readonly toastrService: ToastrService,
    private readonly tokenService: TokenService,
    private readonly viewportScroller: ViewportScroller,
  ) {
    const { clientId, accessToken } = this.getTokens();
    this.tokenForm = new FormGroup({
      clientId: new FormControl(clientId, [Validators.required, Validators.pattern('^[0-9a-z]{30}$')]),
      accessToken: new FormControl(accessToken, [Validators.required, Validators.pattern('^[0-9a-z]{30}$')]),
      remember: new FormControl(true),
    });
  }

  public async ngOnInit(): Promise<void> {
    this.topics = await this.documentationService.getAllTopics();
    this.latestCommits = this.gitService.getLatestCommits(5);

    this.subscriptions.push(
      this.viewportService.getBreakpointObservable().subscribe(
        (breakpoint: IViewportBreakpoint) => this.size = breakpoint === 'xs' ? 'none' : 'large'
      )
    );
  }

  public toggleCollapse(): void {
    this.isCollapsed = !this.isCollapsed;

    if (this.isCollapsed) {
      this.viewportScroller.scrollToPosition([0, 0]);
    } else {
      setTimeout(
        () => this.viewportScroller.scrollToPosition([0, this.collapse.nativeElement.getBoundingClientRect().top - 80])
      );
    }
  }

  public getTokens(): IToken {
    return this.tokenService.getTokens();
  }

  public setTokens(): void {
    const tokens: IToken = {
      clientId: this.tokenForm.get('clientId')?.value || '',
      accessToken: this.tokenForm.get('accessToken')?.value || ''
    }
    const { value: remember } = this.tokenForm.get('remember')?.value;

    this.tokenService.setTokens(tokens, remember);
  }

  public deleteTokens(): void {
    this.tokenService.clearTokens();
    this.tokenForm.get('clientId')?.setValue('');
    this.tokenForm.get('accessToken')?.setValue('');
    this.tokenForm.get('remember')?.setValue(true);
    this.toastrService.success('Your tokens are deleted!');
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }
}
