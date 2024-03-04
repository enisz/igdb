import { DatePipe } from '@angular/common';
import { AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { NgbScrollSpyModule } from '@ng-bootstrap/ng-bootstrap';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { TopicDocumentMethods, TopicDocumentType } from '../../../database/document/topic.document';
import { AgoPipe } from '../../../pipe/ago.pipe';
import { CustomDatePipe } from '../../../pipe/custom-date.pipe';
import { TokenPipe } from '../../../pipe/token.pipe';
import { DocumentationService } from '../../../service/documentation.service';
import { ScrollSpyService } from '../../../service/scroll-spy.service';
import { SectionComponent } from '../section/section.component';

@Component({
  selector: 'app-topic',
  standalone: true,
  imports: [SectionComponent, AgoPipe, DatePipe, TokenPipe, NgbScrollSpyModule, CustomDatePipe],
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
