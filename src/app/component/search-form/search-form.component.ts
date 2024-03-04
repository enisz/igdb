import { Component } from '@angular/core';
import { SearchService } from '../../service/search.service';

@Component({
  selector: 'app-search-form',
  standalone: true,
  imports: [],
  templateUrl: './search-form.component.html',
  styleUrl: './search-form.component.scss'
})
export class SearchFormComponent {
  public constructor(
    private searchService: SearchService
  ) { }

  public handleClick(event: MouseEvent): void {
    event.preventDefault();
    (event.currentTarget as HTMLInputElement).blur();
    this.searchService.setModalVisibility(true);
  }
}
