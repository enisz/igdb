import { Component, Input, OnInit } from '@angular/core';
import { DocumentationService } from '../../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { SectionComponent } from '../section/section.component';
import { NgbScrollSpyModule, NgbScrollSpyService } from '@ng-bootstrap/ng-bootstrap';
import { AgoPipe } from '../../../pipe/ago.pipe';
import { DatePipe } from '@angular/common';

@Component({
  selector: 'app-topic',
  standalone: true,
  imports: [SectionComponent, NgbScrollSpyModule, AgoPipe, DatePipe],
  templateUrl: './topic.component.html',
  styleUrl: './topic.component.scss'
})
export class TopicComponent implements OnInit {
  @Input('topic-id') public topicId = '';
  public topic!: RxDocument<TopicDocumentType, TopicDocumentMethods>;
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];

  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly ngbScrollSpyService: NgbScrollSpyService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.topic = await this.documentationService.getTopic(this.topicId);
    this.sections = await this.documentationService.getSectionsByTopic(this.topic.id);

    const mydate = this.topic.date;

    // console.log(mydate, Date.parse(mydate as string ));
    // this.ngbScrollSpyService.active$.subscribe(
    //   (id: string) => {
    //     console.log('active$: ' + id);

    //   }
    // )
    // this.ngbScrollSpyService.start();
  }
}
