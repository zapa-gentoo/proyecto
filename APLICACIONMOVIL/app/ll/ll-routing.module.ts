import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { LlPage } from './ll.page';

const routes: Routes = [
  {
    path: '',
    component: LlPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class LlPageRoutingModule {}
