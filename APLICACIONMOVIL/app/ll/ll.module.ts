import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { LlPageRoutingModule } from './ll-routing.module';

import { LlPage } from './ll.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    LlPageRoutingModule
  ],
  declarations: [LlPage]
})
export class LlPageModule {}
