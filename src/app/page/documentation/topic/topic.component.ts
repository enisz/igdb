import { AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { DocumentationService } from '../../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { SectionComponent } from '../section/section.component';
import { AgoPipe } from '../../../pipe/ago.pipe';
import { DatePipe } from '@angular/common';
import { TokenPipe } from '../../../pipe/token.pipe';
import { NgbScrollSpyModule } from '@ng-bootstrap/ng-bootstrap';
import { ScrollSpyService } from '../../../service/scroll-spy.service';

@Component({
  selector: 'app-topic',
  standalone: true,
  imports: [SectionComponent, AgoPipe, DatePipe, TokenPipe, NgbScrollSpyModule],
  templateUrl: './topic.component.html',
  styleUrl: './topic.component.scss'
})
export class TopicComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input('topic-id') public topicId = '';
  @ViewChild('element') private element!: ElementRef;
  public topic!: RxDocument<TopicDocumentType, TopicDocumentMethods>;
  public sections: RxDocument<SectionDocumentType, SectionDocumentMethods>[] = [];

  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly scrollSpyService: ScrollSpyService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.topic = await this.documentationService.getTopic(this.topicId);
    this.sections = await this.documentationService.getSectionsByTopic(this.topic.id);
  }

  public ngAfterViewInit(): void {
    setTimeout(() => this.scrollSpyService.observe(this.element.nativeElement));
  }

  public ngOnDestroy(): void {
    this.scrollSpyService.unobserve(this.element.nativeElement);
  }
}
