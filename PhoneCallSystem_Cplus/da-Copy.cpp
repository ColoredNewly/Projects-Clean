#include <iostream>
#include <fstream>
#include <cstring>
#include <string>
using namespace std;

struct PhoneRecord {
    char ime[15];
    char prezime[15];
    char brojj[11];
    int vremetraenje;
    int data;
};

// FUNKCIJA SHTO KJE SE UPTOREBUVA NA VALIDACIJA NA ime[15] i prezime[15]
bool IsAlpha(char zbor[]){ // FUNKCIJA SHO KJE VRNE FALSE AKO BAREM EDEN OD ELEMENTITE NA DADENIOT STRING (CHAR []) NE E BUKVA
	bool da = true; 
	int strl = strlen(zbor);// KOLKU ELEMENTI IMA STRINGOT(CHAR[]), ODNOSNO zbor;
	for(int i = 0; i<strl; i+=1){//FOR LOOP SHTO ZA SEKOJ OD ELEMENTITE KJE PROVERI DALI SE BUKVI
		if(!isalpha(zbor[i])){// AKO IMA EDEN ELEMENT SHTO NE E BUKVA
			da = false; // KJE VRATI FALSE
			break; // I KJE IZLEZE OD LOOPOT
		}
	}
	return da;
}

// FUNKCIJA SHTO KJE SE UPOTREBUVA ZA VALIDACIJA NA brojj[11]
bool ValidBroj(char zbor[]){ 
	bool da = true;
	for(int i = 0; i<11; i+=1){
		if(i==3||i==7){// NA ELEMENTITE SO POZICIJA 3 I 7(KADE SHTO SE NAOGJA -),
			if(zbor[i]!='-'){//AKO N E CRTA
				da = false;// DA VRATI FALSE
				break;
			}
		}
		else{
			if(!isdigit(zbor[i])){//ZA OSTANATITE ELEMTI PROVERUVA DALI SE BROJKI, AKO BAREM EDNA NE E BROJKA, VRNE FALSE
				da = false;
				break;
			}
		}
	}
	return da;
}

// FUNCIJA ZA ZAMENUVANJE NA MESTATA NA DVA PhoneRecord PROMENLIVI VO DADENA NIZA
void Zameni(PhoneRecord niza[], int i, int j){
	PhoneRecord temp = niza[i];
	niza[i] = niza[j];
	niza[j] = temp;
}

void TOLOWER(char A[]){//FUNKCIJA ZA SITE ELEMENTI VO DADEN STRING(CHAR[]), DA GI PRETVORI VO MALI BUKVI
	int len = strlen(A);//VAZNO:::VO C++ KOGA NIZA(char[]) SE DAVA NA FUKNCIJA, AVTOMATSKI SE DAVA SO REFERENCA(promenite na nizata vnatre vo fukncijata kje se napravat i nadvor od nea)
	for(int i = 0; i<len; i+=1){
		A[i] = tolower(A[i]);
	}
}

void Sortiraj(PhoneRecord niza[], int numEle){//
	for(int i = 0; i<numEle-1; i+=1){
		for(int j = i+1; j<numEle; j+=1){
			char prezime1[15]; 
			char prezime2[15];
			char ime1[15];
			char ime2[15];
			char broj1[11];
			char broj2[11];
			strcpy(prezime1, niza[i].prezime);
			strcpy(prezime2, niza[j].prezime);
			strcpy(ime1, niza[i].ime);
			strcpy(ime2, niza[j].ime);
			strcpy(broj1, niza[i].brojj);
			strcpy(broj2, niza[j].brojj);
			TOLOWER(prezime1);//za efikasno da se sortiraat preku ascii vrednosti treba site da se ili uppercase ili lowercase,
			TOLOWER(prezime2);//zatoa sozdavat se novi preminlivi, prvo kopija na tie spored dadenata iteracija, a potoa gi pretvarame vo istite no site bukvi vo lowercase
			TOLOWER(ime1);
			TOLOWER(ime2);
			TOLOWER(broj1);
			TOLOWER(broj2);
	// isto kako niza[i].prezime, niza[j].prezime
			if(strcmp(prezime1, prezime2) > 0){//sporeduva po ASCII vrednosti, minusira ja vrednosta na prvio od vrednosta na vtorio, ako se dobie pozitiven broj znachi deka vrednosta na prvio e pogolema od vrednosta na vtorio, a spoerd ASCII, bukvata a ima pomala vrednost od drugite i taka natamu, zatoa ako e b-a, kje se dobie pozitiven broj, a ako e a-b kje se dobie negativen, a bidejki sortirame, vo toj slucaj kje gi zamenime vrednostite
				Zameni(niza, i, j);
			}	
			else if(strcmp(prezime1, prezime2) == 0){//ako se ednakvi, odeme da sporeduvame ime i taka natamu
				if(strcmp(ime1, ime2) > 0){
					Zameni(niza, i, j);
				}
				else if(strcmp(ime1, ime2) == 0){
					if(strcmp(broj1, broj2) < 0){
						Zameni(niza, i, j);
					}
				}
			}
		}	
	}
}


int CENA(PhoneRecord broj){
	int cena;
	if(broj.vremetraenje<60)//ako e pomalce od minuta, samo 40 da koshta
		cena = 40;
	else if(broj.vremetraenje>5999){//ako ima barem 1 saat, minutite posle taj saat * 100 da koshtat, cim ima nad 5999, togash stanuva 6000, vo MMSS forma toa e 60 minuti i 00 sekundi.
		int minuti = (broj.vremetraenje-6000) / 100;//ako e primer 6500, -6000 kje stane 500. /100 kje stane 5. i sme dobile koklu minuti treba da pomnozime so 100, kako shto veli vo word dokumento
		cena = minuti * 100;
		cena += 60 * 40;// a prethodnite minuti da si koshtaat obicno po 40
	}	
	else if(broj.vremetraenje>100){//ako ima barem edna minuta, sekoja minuta 40 da koshta
		int minuti = broj.vremetraenje/100;
		cena = minuti * 40;
	}	
	return cena;
}

int main(){
	
	PhoneRecord obicni[180];
	PhoneRecord vip[20];
	
	
	int numObicni = 0;//na sekoja iteracija kje se zgolemuvat, spored toa kakov vid na broj e vnesen
	int numVip = 0;
	
	ofstream datoteka;//otvarame datotekata
	datoteka.open("SE.dat");
	
	int vkupno = 4;
//	cout<<"Broj na rekordi(najmnogu 200): ";
//	cin>>vkupno;
	
	int DVAESE=20;
	while(numObicni+numVip<vkupno){
		cout<<"============================"<<numObicni<<"=========================="<<numVip<<endl;
		
		PhoneRecord broj;
		
		int choice = 0;
		
		if(numVip<20){
			cout<<"Vnesi vid na korisnik(0 za obicen, 1 za vip): ";
			cin>>choice;
			while(choice!=1 && choice!=0){//kje izvrshuva se dodeka korisniko ne vnese ili 1 ili 0
				cout<<"Vnesi ili 0 ili 1: ";
				cin>>choice;
			}
		}
		else if(numVip==DVAESE){
			cout<<"Dostignavte maximum broj na vip korisnici(20). Korisnicite shto kje prodolzite da gi vnesuvate kje se od obichen tip"<<endl;
			DVAESE+=1;//za vishe numVip da ne e ednakvo so DVAESE(od tuka pa natamu, numVip kje si ostane 20, a DVAESE kje stane 21, ne se ednakvi taka da samo ednshak kje se izvrshe ovaj cout)
		}
		
		cout<<"Vnesi ime: ";
		cin>>broj.ime;
		bool da = IsAlpha(broj.ime);//povikuva se fukncijata od ozgore
		while(strlen(broj.ime)>15 || da==false){//ako e pogolem od 15 bukvi i ako ima barem eden element sho ne e bukva
			cout<<"Imeto mora da ima najmnogu 15 bukvi: "; // kje se izvrshuva ovoa
			cin>>broj.ime;// pa go povbikuva da vnese, pa pa proveruva toa gore sho e i se taka se dodeka ne vnese kako sho treba(da e do 15 bukci, i site da mu se bukvi)
			da = IsAlpha(broj.ime);
		}
		
		cout<<"Vnesi prezime: ";
		cin>>broj.prezime;
		da = IsAlpha(broj.prezime);
		while(strlen(broj.prezime)>15 || da==false){
			cout<<"Prezimeto mora da ima najmnogu 15 bukvi: ";
			cin>>broj.prezime;
			da = IsAlpha(broj.prezime);
		}
		
		cout<<"Vnesi telefonski broj(vo forma XXX-XXX-XXX): ";
		cin>>broj.brojj;
		da = ValidBroj(broj.brojj);
		while(strlen(broj.brojj)!=11 || da==false){
			cout<<"Telefonskiot broj mora da bide napishan vo forma(XXX-XXX-XXX): ";
			cin>>broj.brojj;
			da = ValidBroj(broj.brojj);
		}
		
		cout<<"Vnesi vremetraenje na razgovor(vo forma MMSS): ";
		cin>>broj.vremetraenje;
		int sek = broj.vremetraenje%100;// za da gi dobie poslednite dve brojki od vnesenite MM(SS)
		while(broj.vremetraenje>9999 || sek>59){
			cout<<"Vremetraenjeto mora da bide zapishano vo forma MMSS i sekundite ne smeat da bidat nad 59: ";
			cin>>broj.vremetraenje;
			sek = broj.vremetraenje%100;
		}
		
		
		if(choice){
			vip[numVip] = broj;//AKO IZBORO E 1, U NIZATA vip[] SE VENSUVA BROJO
			numVip+=1;
		}
		else{//AKO E 0, U OBICHEN
			obicni[numObicni] = broj;
			numObicni+=1;
		}
		
		
		if(datoteka.is_open()){//ZAPISHUVA U DATOTEKA VNESENIO BROJ
			datoteka<<"Tip: ";if(choice==0)datoteka<<"Obicen"<<endl;else datoteka<<"Vip"<<endl;
			datoteka<<"Ime: "<<broj.ime<<endl<<"Prezime: "<<broj.prezime<<endl<<"Broj: "<<broj.brojj<<endl<<"Vremetraenje: "<<broj.vremetraenje<<endl;
			datoteka<<endl;
			cout<<"\n";
		}

	}
	datoteka.close();
	
	
	Sortiraj(vip, numVip);// SORTIRA VIP NIZA
	Sortiraj(obicni, numObicni); //SORTIRA OBICNA NIZA
	
	datoteka.open("sort.dat");//OTVARAR NOVA DATOTEKA sort.dat
	if(datoteka.is_open()){
		for(int i = 0; i<numVip; i+=1){// ZA PISUVANJE NA SEKOJ BROJ OD VIP NIZATA
			PhoneRecord brojjj = vip[i];
			datoteka<<"Tip: Vip"<<endl<<"Prezime: "<<brojjj.prezime<<endl<<"Ime: "<<brojjj.ime<<endl<<"Broj: "<<brojjj.brojj<<endl
			<<"Vremetraenje: "<<brojjj.vremetraenje<<endl<<"Cena: Neograniceno"<<endl;
			datoteka<<endl;
		}
		for(int i = 0; i<numObicni; i+=1){//ZA PISUVANJE NA SEKOJ BROJ OD OBICNATA NIZA
			PhoneRecord brojjj = obicni[i];
			datoteka<<"Tip: Obicen"<<endl<<"Prezime: "<<brojjj.prezime<<endl<<"Ime: "<<brojjj.ime<<endl<<"Broj: "<<brojjj.brojj<<endl
			<<"Vremetraenje: "<<brojjj.vremetraenje<<endl<<"Cena: "<<CENA(brojjj)<<endl;
			datoteka<<endl;
		}
		
	}
	datoteka.close();
	

	return 0;
}
