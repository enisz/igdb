import { Component, OnDestroy, OnInit } from '@angular/core';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { RxDocument } from 'rxdb';
import { TopicDocumentType, TopicDocumentMethods } from '../../database/document/topic.document';
import { PageFooterComponent } from '../../component/page-footer/page-footer.component';
import { RouterLink } from '@angular/router';
import { DocumentationService } from '../../service/documentation.service';
import { ICommits } from '../../interface/git.interface';
import { GitService } from '../../service/git.service';
import { CommonModule } from '@angular/common';
import { ViewportService } from '../../service/viewport.service';
import { IViewportBreakpoint } from '../../interface/viewport.interface';
import { Subscription } from 'rxjs';
import { NtkmeButtonModule } from '@ctrl/ngx-github-buttons';
import { SearchFieldDirective } from '../../directive/search-field.directive';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [TopBarComponent, PageFooterComponent, RouterLink, CommonModule, NtkmeButtonModule, SearchFieldDirective],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit, OnDestroy {
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public latestCommits!: Promise<ICommits[]>;
  public user = 'enisz';
  public repo = 'igdb';
  public count = true;
  public size: 'none' | 'large' = 'large';
  public types: ('star' | 'follow' | 'watch' | 'fork' | 'issue' | 'download')[] = ['follow', 'star', 'watch'];
  private subscriptions: Subscription[] = [];
  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly gitService: GitService,
    private readonly viewportService: ViewportService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.topics = await this.documentationService.getAllTopics();
    this.latestCommits = this.gitService.getLatestCommits(5);

    this.subscriptions.push(
      this.viewportService.getBreakpointObservable().subscribe(
        (breakpoint: IViewportBreakpoint) => this.size = breakpoint === 'xs' ? 'none' : 'large'
      )
    );
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }
}
