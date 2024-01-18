import { CommonModule, ViewportScroller } from '@angular/common';
import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { NtkmeButtonModule } from '@ctrl/ngx-github-buttons';
import { NgbCollapse, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { RxDocument } from 'rxdb';
import { Subscription } from 'rxjs';
import { PageFooterComponent } from '../../component/page-footer/page-footer.component';
import { SearchFormComponent } from '../../component/search-form/search-form.component';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { TopicDocumentMethods, TopicDocumentType } from '../../database/document/topic.document';
import { ICommits } from '../../interface/git.interface';
import { IToken } from '../../interface/token.interface';
import { IViewportBreakpoint } from '../../interface/viewport.interface';
import { DocumentationService } from '../../service/documentation.service';
import { GitService } from '../../service/git.service';
import { ToastService } from '../../service/toast.service';
import { TokenService } from '../../service/token.service';
import { ViewportService } from '../../service/viewport.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [TopBarComponent, PageFooterComponent, RouterLink, CommonModule, NtkmeButtonModule, NgbCollapse, ReactiveFormsModule, NgbTooltipModule, SearchFormComponent],
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
  private tokenValidatorPattern = '^[0-9a-z]*$';
  public tokenForm: FormGroup;
  private subscriptions: Subscription[] = [];
  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly gitService: GitService,
    private readonly viewportService: ViewportService,
    private readonly tokenService: TokenService,
    private readonly viewportScroller: ViewportScroller,
    private readonly toastService: ToastService,
  ) {
    const { clientId, accessToken } = this.getTokens();
    this.tokenForm = new FormGroup({
      clientId: new FormControl(clientId, [Validators.required, Validators.minLength(30), Validators.maxLength(30), Validators.pattern(this.tokenValidatorPattern)]),
      accessToken: new FormControl(accessToken, [Validators.required, Validators.minLength(30), Validators.maxLength(30), Validators.pattern(this.tokenValidatorPattern)]),
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

  public getValidationErrors(controlName: string): string[] {
    const control = this.tokenForm.get(controlName);

    if (control && control.errors !== null) {
      return Object.keys(control.errors);
    }

    return [];
  }

  public toggleCollapse(): void {
    this.isCollapsed = !this.isCollapsed;

    if (this.isCollapsed) {
      this.viewportScroller.scrollToPosition([0, 0]);
      this.resetTokenForm();
    } else {
        setTimeout(
          () => this.viewportScroller.scrollToPosition([0, this.collapse.nativeElement.getBoundingClientRect().top - 90])
        );
    }
  }

  public getTokens(): IToken {
    return this.tokenService.getTokens();
  }

  public setTokens(): void {
    const clientId = this.tokenForm.get('clientId')?.value || '';
    const accessToken = this.tokenForm.get('accessToken')?.value || '';
    const remember = this.tokenForm.get('remember')?.value;

    this.tokenService.setTokens(clientId, accessToken, remember);
    this.tokenForm.markAsUntouched();
    this.toastService.success('Tokens saved succesfully!');
  }

  public deleteTokens(): void {
    this.tokenService.clearTokens();
    this.resetTokenForm();
    this.toastService.info('Tokens deleted successfuly!');
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  private resetTokenForm(): void {
    const { clientId, accessToken } = this.tokenService.getTokens();
    const remember = this.tokenService.isRemembered() || true;
    this.tokenForm.reset({ clientId, accessToken, remember });
  }

  private tokensAlreadySavedValidator(form: AbstractControl): ValidationErrors | null {
    const error: ValidationErrors = {};

    const fromClientId = form.get('clientId')?.value;
    const formAccessToken = form.get('accessToken')?.value;
    const { clientId, accessToken } = this.tokenService.getTokens();

    if (fromClientId === clientId && formAccessToken === accessToken) {
      error['alreadySaved'] = true;
    }

    return Object.keys(error).length ? error : null;
  }
}
