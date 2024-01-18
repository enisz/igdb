import { HttpClient, provideHttpClient } from '@angular/common/http';
import { APP_INITIALIZER, ApplicationConfig, isDevMode } from '@angular/core';
import { provideAnimations } from '@angular/platform-browser/animations';
import { provideRouter, withInMemoryScrolling, withRouterConfig } from '@angular/router';
import { provideServiceWorker } from '@angular/service-worker';
import { RxDumpDatabaseAny } from 'rxdb';
import { firstValueFrom } from 'rxjs';
import { routes } from './app.routes';
import { DocumentationDatabaseCollections } from './database/database';
import { DatabaseService } from './service/database.service';


export const appConfig: ApplicationConfig = {
    providers: [
        provideRouter(
            routes,
            withInMemoryScrolling({ anchorScrolling: 'enabled', scrollPositionRestoration: 'top' }),
            withRouterConfig({ onSameUrlNavigation: 'ignore' }),
        ),
        provideHttpClient(),
        {
            provide: APP_INITIALIZER,
            useFactory: (httpClient: HttpClient, databaseService: DatabaseService) => async (): Promise<any> => databaseService.build(
                await firstValueFrom(httpClient.get('assets/database.json')) as RxDumpDatabaseAny<DocumentationDatabaseCollections>
            ),
            deps: [HttpClient, DatabaseService],
            multi: true,
        },
        provideServiceWorker('ngsw-worker.js', {
            enabled: !isDevMode(),
            registrationStrategy: 'registerWhenStable:30000'
        }),
        provideAnimations(),
    ],
};
