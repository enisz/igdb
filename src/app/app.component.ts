  import { CommonModule } from '@angular/common';
import { APP_INITIALIZER, ApplicationInitStatus, Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Subscription } from 'rxjs';
import { SearchModalComponent } from './component/search-modal/search-modal.component';
import { ToastContainerComponent } from './component/toast-container/toast-container.component';
import { NetworkService } from './service/network.service';
import { ToastService } from './service/toast.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterOutlet, SearchModalComponent, ToastContainerComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit, OnDestroy {
  private subscriptions: Subscription[] = [];

  public constructor(
    @Inject(APP_INITIALIZER) public applicationInitStatus: ApplicationInitStatus,
    private readonly networkService: NetworkService,
    private readonly toastService: ToastService,
  ) {}

  public ngOnInit(): void {
    this.subscriptions.push(
      this.networkService.getStatusObservable().subscribe(
        (online: boolean) => {
          if (!online) {
            this.toastService.warning('Network connection lost!');
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
