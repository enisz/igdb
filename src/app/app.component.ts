  import { APP_INITIALIZER, ApplicationInitStatus, Component, HostListener, Inject, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { NetworkService } from './service/network.service';
import { Subscription } from 'rxjs';
import { ToastrService } from 'ngx-toastr';
import { SearchModalComponent } from './component/search-modal/search-modal.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterOutlet, SearchModalComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit, OnDestroy {
  private subscriptions: Subscription[] = [];

  public constructor(
    @Inject(APP_INITIALIZER) public applicationInitStatus: ApplicationInitStatus,
    private readonly networkService: NetworkService,
    private readonly toastrService: ToastrService,
  ) {}

  public ngOnInit(): void {
    this.subscriptions.push(
      this.networkService.getStatusObservable().subscribe(
        (online: boolean) => {
          if (!online) {
            this.toastrService.warning('No network connection detected!');
          }
        }
      )
    );
  }

  public ngOnDestroy(): void {
    for (const subscription of this.subscriptions) {
      subscription?.unsubscribe();
    }
  }
}
