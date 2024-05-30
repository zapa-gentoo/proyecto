import { Component, OnInit } from '@angular/core';
import { NavController } from '@ionic/angular';
import { AlertController,LoadingController } from '@ionic/angular';  
import { HttpClient } from '@angular/common/http';  //Importamos modulo Http
import { Router } from '@angular/router';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
})
export class HomePage implements OnInit {

  usuario: String = "";
  password: String = "";
  error: number = 0;

  constructor(public navCtrl:NavController, private loadingController:LoadingController, private http: HttpClient, public alertCtrl: AlertController, private router: Router) {}
  ngOnInit(): void {
  }

  LoginButton():void {
    

      this.presentWait("show","...Identificandose...",3000);
      this.http
      .get('https://www.sourcenet.es/PANEL/TECNICO/curl_login_json.php?u='+this.usuario+'&p='+this.password)
      .subscribe((response:any) => {
        //console.log(response);

           if(response['result']=="OK") {
             this.presentWait("dismiss","",0);
             this.router.navigate(['activar'])
           }
 
           if(response['result']=="ERROR") {
             this.presentAlert("LOGIN","Datos incorrectos");
           }
         
         
       },(error)=> {
         this.presentWait("dissmiss","",0);
         this.error+1;
         this.presentAlert("ERROR","Error de comunicación de red");
      });

    this.navCtrl.navigateForward('home');

  }





  async presentWait(action: string,mensaje: string, duracion: number) {

    var loading = await this.loadingController.create({
      message: mensaje,
      duration: duracion
      });


     switch(action) {

        case "show":
         loading.present();
        break;

        case "dismiss":
         this.loadingController.dismiss();
        break;

    }


  }

  async presentAlert(titulo:string, message: string) {

      const alert = await this.alertCtrl.create({
        header: titulo,
        message: message,
        buttons: ['OK']
      });

      await alert.present();

  }



}
