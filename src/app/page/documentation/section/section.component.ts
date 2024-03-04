import { AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';
import { SafeHtmlPipe } from '../../../pipe/safe-html.pipe';
import { TokenPipe } from '../../../pipe/token.pipe';
import { DocumentationService } from '../../../service/documentation.service';
import { ScrollSpyService } from '../../../service/scroll-spy.service';

@Component({
  selector: 'app-section',
  standalone: true,
  imports: [TokenPipe, SafeHtmlPipe],
  templateUrl: './section.component.html',
  styleUrl: './section.component.scss',
  encapsulation: ViewEncapsulation.None,
})
export class SectionComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input('section-id') public sectionId = '';
  @ViewChild('element') public element!: ElementRef<HTMLDivElement>;
  public section!: RxDocument<SectionDocumentType, SectionDocumentMethods>;
  public constructor(
    private readonly documentationService: DocumentationService,
    private readonly scrollSpyService: ScrollSpyService,
  ) {}

  public async ngOnInit(): Promise<void> {
    this.section = await this.documentationService.getSection(this.sectionId);
  }

  public ngAfterViewInit(): void {
    setTimeout(() => this.scrollSpyService.observe(this.element.nativeElement));
  }

  public ngOnDestroy(): void {
    this.scrollSpyService.unobserve(this.element.nativeElement);
  }
}
