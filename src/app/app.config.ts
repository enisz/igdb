import { APP_INITIALIZER, ApplicationConfig, importProvidersFrom, isDevMode } from '@angular/core';
import { provideRouter, withInMemoryScrolling, withRouterConfig } from '@angular/router';
import { routes } from './app.routes';
import { HttpClient, provideHttpClient } from '@angular/common/http';
import { RxDumpDatabaseAny } from 'rxdb';
import { firstValueFrom, of } from 'rxjs';
import { DocumentationDatabaseCollections } from './database/database';
import { DatabaseService } from './service/database.service';
import { provideServiceWorker } from '@angular/service-worker';
import { provideAnimations } from '@angular/platform-browser/animations';


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
