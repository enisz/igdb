import { Component, Input, OnInit } from '@angular/core';
import { DocumentationService } from '../../../service/documentation.service';
import { RxDocument } from 'rxdb';
import { SectionDocumentMethods, SectionDocumentType } from '../../../database/document/section.document';

@Component({
  selector: 'app-section',
  standalone: true,
  imports: [],
  templateUrl: './section.component.html',
  styleUrl: './section.component.scss'
})
export class SectionComponent implements OnInit {
  @Input('section-id') public sectionId = '';
  public section!: RxDocument<SectionDocumentType, SectionDocumentMethods>;
  public constructor(
    private readonly documentationService: DocumentationService,
  ) {}

  public async ngOnInit(): Promise<void> {
    this.section = await this.documentationService.getSection(this.sectionId);
  }
}
