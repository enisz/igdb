import { Component, OnInit } from '@angular/core';
import { TopBarComponent } from '../../component/top-bar/top-bar.component';
import { PageHeaderComponent } from '../../component/page-header/page-header.component';
import { RxDocument } from 'rxdb';
import { TopicDocumentType, TopicDocumentMethods } from '../../database/document/topic.document';
import { PageFooterComponent } from '../../component/page-footer/page-footer.component';
import { RouterLink } from '@angular/router';
import { DocumentationService } from '../../service/documentation.service';
import { ICommits } from '../../interface/git.interface';
import { GitService } from '../../service/git.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [TopBarComponent, PageHeaderComponent, PageFooterComponent, RouterLink, CommonModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent implements OnInit {
  public topics: RxDocument<TopicDocumentType, TopicDocumentMethods>[] = [];
  public latestCommits!: Promise<ICommits[]>;
  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly gitService: GitService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.topics = await this.documentationService.getAllTopics();
    this.latestCommits = this.gitService.getLatestCommits(5);
  }
}
