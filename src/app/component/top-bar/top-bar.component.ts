import { CommonModule, DatePipe } from '@angular/common';
import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { NgbDropdownModule, NgbTooltipModule } from '@ng-bootstrap/ng-bootstrap';
import { Subscription } from 'rxjs';
import { IScrollPosition } from '../../interface/viewport.interface';
import { NetworkService } from '../../service/network.service';
import { ToastService } from '../../service/toast.service';
import { ViewportService } from '../../service/viewport.service';
import { SearchFormComponent } from '../search-form/search-form.component';

@Component({
  selector: 'app-top-bar',
  standalone: true,
  imports: [CommonModule, RouterLink, NgbDropdownModule, NgbTooltipModule, DatePipe, SearchFormComponent],
  templateUrl: './top-bar.component.html',
  styleUrl: './top-bar.component.scss'
})
export class TopBarComponent implements OnInit, OnDestroy {
  @Input('hamburger') public hamburger = false;
  @Input('searchbar') public searchbar = false;
  @Input('progressbar') public progressbar = false;
  private subscriptions: Subscription[] = [];
  public isOnline = true;
  public percentage = 0;

  public constructor(
    private readonly networkService: NetworkService,
    private readonly toastService: ToastService,
    private readonly viewportService: ViewportService,
  ) { }

  public async ngOnInit(): Promise<void> {
    this.subscriptions.push(
      this.networkService.getStatusObservable().subscribe(
        (online: boolean) => this.isOnline = online
      )
    );

    this.subscriptions.push(
      this.viewportService.getScrollObservable().subscribe(
        (scroll: IScrollPosition) => this.percentage = scroll.scrollPercentage
      )
    );
  }

  ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
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

  public downloadNotification(): void {
    this.toastService.info('Downloading latest release from git...');
  }
}
