import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { NetworkService } from '../../service/network.service';
import { Subscription } from 'rxjs';
import { GitService } from '../../service/git.service';
import { IRelease } from '../../interface/git.interface';
import { CommonModule, DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { NgbDropdownModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { SearchFieldDirective } from '../../directive/search-field.directive';

@Component({
  selector: 'app-top-bar',
  standalone: true,
  imports: [CommonModule, RouterLink, NgbDropdownModule, SearchFieldDirective, NgbTooltipModule, DatePipe],
  templateUrl: './top-bar.component.html',
  styleUrl: './top-bar.component.scss'
})
export class TopBarComponent implements OnInit, OnDestroy {
  @Input('hamburger') public hamburger = false;
  @Input('searchbar') public searchbar = false;
  public latestRelease!: IRelease;
  private subscriptions: Subscription[] = [];
  public isOnline = true;

  public constructor(
    private readonly networkService: NetworkService,
    private readonly gitService: GitService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.latestRelease = await this.gitService.getLatestRelease();
    this.subscriptions.push(
      this.networkService.getStatusObservable().subscribe(
        (online: boolean) => this.isOnline = online
      )
    );
  }

  ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }

  public downloadLatestRelease(): any {
    console.log(this.latestRelease);
  }

  public toggleSidebar(): void {
    const sidebarElement = document.getElementById('docs-sidebar') as HTMLDivElement;
    const sidebarVisible = sidebarElement.classList.contains('sidebar-visible');

    if(sidebarVisible) {
      sidebarElement.classList.remove('sidebar-visible');
      sidebarElement.classList.add('sidebar-hidden');
    } else {
      sidebarElement.classList.remove('sidebar-hidden');
      sidebarElement.classList.add('sidebar-visible');
    }
  }
}
