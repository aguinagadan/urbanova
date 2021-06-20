<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Language file.
 *
 * @package   theme_remui
 * @copyright 2016 Frédéric Massart
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Ustawienia zaawansowane';
$string['backgroundimage'] = 'Zdjęcie w tle';
$string['backgroundimage_desc'] = 'Obraz do wyświetlenia jako tło witryny. Przesłany tutaj obraz tła zastąpi obraz tła w plikach predefiniowanych motywów.';
$string['brandcolor'] = 'Kolor marki';
$string['brandcolor_desc'] = 'Kolor akcentującu';
$string['bootswatch'] = 'Bootswatch';
$string['bootswatch_desc'] = 'Bootswatch to zestaw zmiennych Bootstrap i css do stylu Bootstrap';
$string['choosereadme'] = '
<div class="about-remui-wrapper text-center">
    <div class="about-remui m-auto" style="max-width: 1000px;">
        <h1 class="text-center">Witaj w Edwiser RemUI</h1><br>
        <h4 class="text-muted">
        Edwiser RemUI to nowa rewolucja w doświadczaniu użytkowania platformy Moogle. Został odpowiednio zaprojektowany, aby podnosić poziom elearningu dzięki
        niestandardowym layoutom, uproszczonej nawigacji, tworzeniu i dostosywaniu treści <br><br>
        Jesteśmy przekonani, że docenisz przemodelowany wygląd!
        </h4>
        <div class="text-center mt-50">
        <img src="' . $CFG->wwwroot . '/theme/remui/pix/screenshot.jpg" class="w-full" alt="Edwiser RemUI screen shot" style="max-width: 100%;"/>
        </div>
        <br><br>
        <div class="text-center">
            <div class="btn-group text-center" role="group" aria-label="...">
              <div class="btn-group mr-1" role="group">
                <a href="https://edwiser.helpscoutdocs.com/collection/78-edwiser-remui-theme" target="_blank" class="btn btn-primary btn-round">FAQ</a>
              </div>
              <div class="btn-group mr-1" role="group">
                <a href="https://edwiser.org/remui/documentation/" target="_blank" class="btn btn-primary btn-round">Dokumentacja</a>
              </div>
              <div class="btn-group" role="group">
                <a href="https://edwiser.org/contact-us/" target="_blank" class="btn btn-primary btn-round">Wsparcie</a>
              </div>
            </div>
        </div>
        <br>
        <h1 class="text-center">Dostosuj motyw</h1>
        <h4 class="text-muted text-center">
            Rozumiemy, że nie każdy system LMS jest taki sam. Będziemy pracować wspólnie z Tobą, aby realizować Twoje potrzeby.
        </h4>
        <br><br>
        <div class="row wdm_generalbox">
            <div class="marketing-spots span3 col-lg-6 col-12">
                <div class="iconsquare">
                    <i class="fa fa-cogs"></i>
                </div>
                <div class="iconbox-content">
                    <h4>Dopasowanie motywu</h4>
                </div>
            </div>
            <div class="marketing-spots span3 col-lg-6 col-12">
                <div class="iconsquare">
                    <i class="fa fa-edit"></i>
                </div>
                <div class="iconbox-content">
                    <h4>Rozwój funkcjonalności</h4>
                </div>
            </div>
            <br>
            <div class="marketing-spots span3 col-lg-6 col-12">
                <div class="iconsquare">
                    <i class="fa fa-link"></i>
                </div>
                <div class="iconbox-content">
                    <h4>Integracja API</h4>
                </div>
            </div>
            <div class="marketing-spots span3 col-lg-6 col-12">
                <div class="iconsquare">
                    <i class="fa fa-life-ring"></i>
                </div>
                <div class="iconbox-content">
                    <h4>Doradztwo związane z LMS</h4>
                </div>
            </div>
        </div>
        <br>
        <br>
        <div class="text-center">
            <a class="btn btn-primary btn-lg" target="_blank" href="https://edwiser.org/contact-us/">Skontaktuj się z nami</a>&nbsp;&nbsp;
        </div>
    </div>
</div>
<br />';
$string['aboutremui'] = 'O Edwiser RemUI';
$string['currentinparentheses'] = '(aktualny)';
$string['configtitle'] = 'Edwiser RemUI';
$string['fontsize'] = 'Podstawowy rozmiar czcionki';
$string['fontsize_desc'] = 'Wpisz rozmiar czcionki w%';
$string['nobootswatch'] = 'Żaden';
$string['pluginname'] = 'Edwiser RemUI';
$string['presetfiles'] = 'Dodatkowe pliki motywu';
$string['presetfiles_desc'] = 'Pliki ustawień można wykorzystać do radykalnej zmiany wyglądu motywu';
$string['preset'] = 'Ustawienia motywu';
$string['preset_desc'] = 'Wybierz ustawienie, aby znacząco zmienić wygląd motywu';
$string['privacy:metadata'] = 'Motyw remui nie przechowuje żadnych danych osobowych dotyczących żadnego użytkownika.';
$string['rawscss'] = 'Czysty SCSS';
$string['rawscss_desc'] = 'Użyj tego pola, aby podać kod SCSS lub CSS, który zostanie wprowadzony na końcu arkusza stylów.';
$string['rawscsspre'] = 'Czysty początkowy SCSS';
$string['rawscsspre_desc'] = 'W tym polu możesz podać inicjujący kod SCSS, który zostanie wstrzyknięty przed wszystkim innym. W większości przypadków będziesz używać tego ustawienia do definiowania zmiennych.';
$string['region-side-pre'] = 'Prawa';
$string['privacy:metadata:preference:draweropennav'] = 'Preferencje użytkownika dotyczące ukrywania lub pokazywania nawigacji w menu szuflady.';
$string['privacy:drawernavclosed'] = 'Obecne preferencje dla szuflady nawigacji są zamknięte.';
$string['privacy:drawernavopen'] = 'Obecne preferencje dla szuflady nawigacji są otwarte.';
$string['cachedef_courses'] = 'Pamięć podręczna na kursy';
$string['cachedef_guestcourses'] = 'Pamięć podręczna dla kursów dla gości';
$string['cachedef_updates'] = 'Pamięć podręczna na aktualizacje';

// Course view preference.
$string['privacy:metadata:preference:course_view_state'] = 'Typ wyświetlania preferowany przez użytkownika dla listy kursów';
$string['course_view_state_description_grid'] = 'Aby wyświetlić kursy w formacie siatki';
$string['course_view_state_description_list'] = 'Aby wyświetlić kursy w formie listy';

// Course view preference.
$string['privacy:metadata:preference:viewCourseCategory'] = 'Typ wyświetlania preferowany przez użytkownika dla listy kursów';
$string['viewCourseCategory_grid'] = 'Aby wyświetlić kursy w formacie siatki';
$string['viewCourseCategory_list'] = 'Aby wyświetlić kursy w formie listy';

// Aside right view preference.
$string['privacy:metadata:preference:aside_right_state'] = 'Określa, czy boczny blok po prawej stronie powinien być otwarty, czy zadokowany';
$string['aside_right_state_'] = 'Aby wyświetlić blok boczny po prawej stronie jako otwarty';
$string['aside_right_state_overrideaside'] = 'Aby wyświetlić blok boczny po prawej stronie jako zadokowany';

// Menu view preference.
$string['privacy:metadata:preference:menubar_state'] = 'Typ wyświetlania preferowany przez użytkownika dla paska menu';
$string['menubar_state_fold'] = 'Aby wyświetlić pasek menu jako złożony';
$string['menubar_state_unfold'] = 'Aby wyświetlić pasek menu jako rozwinięty';
$string['menubar_state_open'] = 'Aby wyświetlić pasek menu jako otwarty';
$string['menubar_state_hide'] = 'Aby wyświetlić pasek menu jako ukryty';

// Profile Page.
$string['administrator'] = 'Administrator';
$string['contacts'] = 'Kontakty';
$string['blogentries'] = 'Wpisy na blogu';
$string['discussions'] = 'Dyskusje';
$string['aboutme'] = 'O mnie';
$string['courses'] = 'Kursy';
$string['interests'] = 'Zainteresowania';
$string['institution'] = 'Departament i instytucja';
$string['location'] = 'Lokalizacja';
$string['description'] = 'Opis';
$string['editprofile'] = 'Edytuj profil';
$string['start_date'] = 'Data rozpoczęcia';
$string['complete'] = 'Zakończ';
$string['surname'] = 'Nazwisko';
$string['actioncouldnotbeperformed'] = 'Akcja nie może być wykonana!';
$string['enterfirstname'] = 'Podaj swoje imię.';
$string['enterlastname'] = 'Podaj swoje nazwisko.';
$string['enteremailid'] = 'Podaj swój adres email';
$string['enterproperemailid'] = 'Podaj swój poprawny adres email.';
$string['detailssavedsuccessfully'] = 'Szczegóły zapisane pomyślnie!';
$string['forgotpassword'] = 'Nie pamiętasz hasła?';

// Left Navigation Drawer.
$string['createarchivepage'] = 'Strona archiwum kursów';
$string['createanewcourse'] = 'Stwórz nowy kurs';
$string['remuisettings'] = 'Ustawienia RemUI';

// Right Navigation Drawer.
$string['navbartype'] = 'Kolor paska nawigacyjnego';
$string['sidebarcolor'] = 'Kolor paska bocznego';
$string['sitecolor'] = 'Kolor strony';
$string['applysitewide'] = 'Zastosuj w obrębie całej strony.';
$string['applysitecolor'] = 'Zastosuj kolor strony';
$string['sidebarpinned'] = 'Przypięty pasek boczny.';
$string['sidebarunpinned'] = 'Pasek boczny odpięty.';
$string['pinsidebar'] = 'Przypnij pasek boczny';
$string['unpinsidebar'] = 'Odepnij pasek boczny';
$string['primary'] = 'Podstawowy';
$string['brown'] = 'brązowy';
$string['cyan'] = 'Cyjan';
$string['green'] = 'Zielony';
$string['grey'] = 'Szary';
$string['indigo'] = 'Indygo';
$string['orange'] = 'Pomarańczowy';
$string['pink'] = 'Różowy';
$string['purple'] = 'Fioletowy';
$string['red'] = 'Czerwony';
$string['teal'] = 'Cyraneczka';
$string['custom-color'] = 'Niestandardowy kolor';
$string['dark'] = 'Ciemny';
$string['light'] = 'Światło';

// General Settings.
$string['generalsettings'] = 'Ustawienia ogólne';
$string['enableannouncement'] = "Aktywuj komunikaty strony";
$string['enableannouncementdesc'] = "Aktywuj komunikaty strony dla odwiedzających/studentów.";
$string['enabledismissannouncement'] = "włączyć oddalenie Ogłoszenie strony";
$string['enabledismissannouncementdesc'] = "Jeśli włączone, zezwól użytkownikom na oddalenie zapowiedź tekst.";

$string['announcementtext'] = "Komunikat";
$string['announcementtextdesc'] = "Komunikat wyświetlany w obrębie strony.";
$string['announcementtype'] = "Typ komunikatu.";
$string['announcementtypedesc'] = "informacja/alert/zagrożenie/sukces";
$string['typeinfo'] = "Komunikat informacyjny";
$string['typedanger'] = "Ważny komunikat";
$string['typewarning'] = "Komunikat ostrzegający";
$string['typesuccess'] = "Komunikat o sukcesie";
$string['enablerecentcourses'] = 'Włącz Ostatnie Kursy';
$string['enablerecentcoursesdesc'] = 'Jeśli aktywne, menu Ostatnie Kursy będzie widoczne w nagłówku.';
$string['enableheaderbuttons'] = 'Pokaż przyciski nagłówka w menu rozwijanym';
$string['enableheaderbuttonsdesc'] = 'Wszystkie przyciski wyświetlane w nagłówku są konwertowane na pojedyncze menu rozwijane.';
$string['mergemessagingsidebar'] = 'Panel korespondencji seryjnej';
$string['mergemessagingsidebardesc'] = 'Scalanie panelu wiadomości na prawym pasku bocznym';
$string['courseperpage'] = 'Kursów na stronę';
$string['courseperpagedesc'] = 'Ilość kursów na stronę wyświetlanych na stronie archiwum.';
$string['none'] = 'Żaden';
$string['fade'] = 'Zgasnąć';
$string['slide-top'] = 'Slide Top';
$string['slide-bottom'] = 'Przesuń do dołu';
$string['slide-right'] = 'Przesuń w prawo';
$string['scale-up'] = 'Powiększać w skali rysunek';
$string['scale-down'] = 'Pomniejszyć';
$string['courseanimation'] = 'Animacja kursu';
$string['courseanimationdesc'] = 'Włączenie tego spowoduje dodanie animacji dla archiwalnych kursów strony';
$string['enablenewcoursecards'] = 'Włącz nowe karty kursów';
$string['enablenewcoursecardsdesc'] = 'Włączenie tego spowoduje wyświetlenie nowych kart kursów na stronie archiwum kursów';
$string['activitynextpreviousbutton'] = 'Włącz przycisk Następna/Poprzednia aktwyność';
$string['activitynextpreviousbuttondesc'] = 'Przycisk Następna/Poprzednia aktywność pojawi się nad aktywnością dla szybkiego przełączania';
$string['disablenextprevious'] = 'Wyłącz';
$string['enablenextprevious'] = 'Włącz';
$string['enablenextpreviouswithname'] = 'Włącz z nazwą aktywności';
$string['logoorsitename'] = 'Wybierz format logo strony';
$string['logoorsitenamedesc'] = 'Możesz zmienić logo nagłówka witryny zgodnie z własnym wyborem. <br /> Dostępne opcje to: Tylko logo - wyświetlone zostanie tylko logo; <br /> Ikona + nazwa strony - Wyświetli się ikona wraz z nazwą witryny.';
$string['onlylogo'] = 'Tylko logo';
$string['iconsitename'] = 'Ikona + nazwa strony';
$string['logo'] = 'Logo';
$string['logodesc'] = 'Możesz dodać logo, które będzie wyświetlane w nagłówku. Uwaga: preferowana wysokość to 50 pikseli. Jeśli chcesz dostosować, możesz to zrobić podając własny kod CSS.';
$string['logomini'] = 'LogoMini';
$string['logominidesc'] = 'Możesz dodać logomini do wyświetlenia w nagłówku, gdy pasek boczny jest zwinięty. Uwaga: preferowana wysokość to 50 pikseli. Jeśli chcesz dostosować, możesz to zrobić podając własny kod CSS.';
$string['siteicon'] = 'Ikona strony';
$string['siteicondesc'] = 'Nie masz logo? Możesz wybrać jedno z tej <a href="https://fontawesome.com/v4.7.0/cheatsheet/" target="_new">listy</a>. <br /> Wpisz tekst po "fa-".';
$string['customcss'] = 'Własny kod CSS';
$string['customcssdesc'] = 'Tutaj możesz dostosować kod CSS. Będzie on miał zastosowanie w obrębie całej witryny.';
$string['favicon'] = 'Favicon';
$string['favicondesc'] = 'Tutaj możesz wstawić favicon swojej strony.';
$string['fontselect'] = 'Wybór czcionki';
$string['fontselectdesc'] = 'Wybierz jedną z czcionek standardowych lub <a href="https://fonts.google.com/" target="_new">Google Fonts</a>. Zapisz, aby wyświetlić opcje do wyboru.';
$string['fonttypestandard'] = 'Czcionka standardowa';
$string['fonttypegoogle'] = 'Google Fonts';
$string['fontname'] = 'Czcionka witryny';
$string['fontnamedesc'] = 'Wpisz dokładną nazwę czcionki, która będzie używana w Moodle.';
$string['googleanalytics'] = 'Google Analytics Tracking ID';
$string['googleanalyticsdesc'] = 'Podaj Google Analytics Tracking ID, aby uruchomić analitykę na stronie. Format ID powienien być następujący: [UA-XXXXX-Y]';
$string['enablecoursestats'] = 'Włącz statystyki kursu';
$string['enablecoursestatsdesc'] = 'Po włączeniu Administrator, Menedżerowie i nauczyciel zobaczą statystyki związane z kursem na stronie kursu.';
$string['enabledictionary'] = 'Włącz Słownik';
$string['enabledictionarydesc'] = 'Jeśli aktywne, funkcja Słownik będzie aktywowana i będzie pokazywać znaczenie zaznaczonego tekstu w oknie popup.';
$string['more'] = 'More...';

// Frontpage Old String
// Home Page Settings.
$string['homepagesettings'] = 'Ustawienia strony głównej';
$string['frontpagedesign'] = 'Projekt strony głównej';
$string['frontpagedesigndesc'] = 'Ta sekcja dotyczy stylu projektowania strony głównej.';
$string['frontpagechooser'] = 'Wybierz projekt strony głównej';
$string['frontpagechooserdesc'] = 'Wybierz projekt strony głównej.';
$string['frontpagedesignold'] = 'Domyślny stary projekt';
$string['frontpagedesignolddesc'] = 'Domyślny pulpit jak poprzedni.';
$string['frontpagedesignnew'] = 'Nowy design';
$string['frontpagedesignnewdesc'] = 'Świeży nowy projekt z wieloma sekcjami. Możesz skonfigurować sekcje indywidualnie na stroniegłównej.';
$string['newhomepagedescription'] = 'Kliknij „Strona główna” na pasku nawigacyjnym, aby przejść do „Kreatora stron głównych” i utworzyć własną stronę główną';
$string['frontpageloader'] = 'Załaduj obraz modułu ładującego na pierwszą stronę';
$string['frontpageloaderdesc'] = 'Zastąpi to domyślny moduł ładujący obrazem';
$string['frontpageimagecontent'] = 'Zawartość nagłówka';
$string['frontpageimagecontentdesc'] = 'Ta sekcja dotyczy górnej części Twojej strony głównej.';
$string['frontpageimagecontentstyle'] = 'Rodzaj';
$string['frontpageimagecontentstyledesc'] = 'Możesz wybrać pomiędzy statycznym a sliderem.';
$string['staticcontent'] = 'Statyczny';
$string['slidercontent'] = 'Slider';
$string['addtext'] = 'Dodaj tekst';
$string['defaultaddtext'] = 'Edukacja to sprawdzona droga do postępu.';
$string['addtextdesc'] = 'Tutaj możesz dodać tekst do wyświetlenia na stronie głównej, najlepiej w HTML.';
$string['uploadimage'] = 'Wgraj opbrazek';
$string['uploadimagedesc'] = 'Możesz wgrać obrazek jako zawartość slidera';
$string['video'] = 'Kod osadzony w iframe';
$string['videodesc'] = ' Tutaj możesz wkleić kod do filmu, który będzie  osadzony w iframe.';
$string['contenttype'] = 'Wybierz rodzaj zawartości';
$string['contentdesc'] = 'Możesz wybrać pomiędzy obrazkiem a linkiem do filmu.';
$string['imageorvideo'] = 'Obraz / wideo';
$string['image'] = 'Obrazek';
$string['videourl'] = 'Link do filmu';
$string['slideinterval'] = 'Odstęp pomiędzy slajdami';
$string['slideintervalplaceholder'] = 'Dodatnia liczba całkowita w milisekundach.';
$string['slideintervaldesc'] = 'Możesz ustawić czas pomiędzy zmianą slajdów. W przypadku, jeśli wybrany jest tylko jeden slajd, opcja ta nie będzie powodowała żadnych efektów.';
$string['slidercount'] = 'Ilość slajdów';
$string['slidercountdesc'] = '';
$string['one'] = '1';
$string['two'] = '2';
$string['three'] = '3';
$string['four'] = '4';
$string['five'] = '5';
$string['eight'] = '8';
$string['twelve'] = '12';
$string['slideimage'] = 'Wgraj obrazki do slidera';
$string['slideimagedesc'] = 'Możesz wgrać obrazek jako zawartość tego slajdu.';
$string['sliderurl'] = 'Dodaj link do przycisku slidera';
$string['slidertext'] = 'Dodaj tekst do slidera';
$string['defaultslidertext'] = '';
$string['slidertextdesc'] = 'Możesz wstawić tekst jako zawartość tego slajdu. Preferowany HTML.';
$string['sliderbuttontext'] = 'Dodaj przycisk tekstowy na slajdzie';
$string['sliderbuttontextdesc'] = 'Możesz dodać tekst na przycisk na tym slajdzie.';
$string['sliderurldesc'] = 'Możesz podać link do strony, do której użytkownik będzie przekierowany po kliknięciu w przycisk.';
$string['sliderautoplay'] = 'Automatyczne przełączanie slajdera';
$string['sliderautoplaydesc'] = 'Wybierz "tak", aby włączyć automatyczne przełączanie slajdów.';
$string['true'] = 'Tak';
$string['false'] = 'Nie';
$string['frontpageblocks'] = 'Zawartość główna';
$string['frontpageblocksdesc'] = 'Możesz wstawić nagłówek na swojej witrynie';
$string['frontpageblockdisplay'] = 'Sekcja O nas';
$string['frontpageblockdisplaydesc'] = 'Możesz pokazać lub ukryć sekcję "O nas", możesz także wyświetlić sekcję "O nas" w formacie siatki "';
$string['donotshowaboutus'] = 'Nie pokazuj';
$string['showaboutusinrow'] = 'Pokaż sekcję w wierszu';
$string['showaboutusingridblock'] = 'Pokaż sekcję w formacie siatki';

// About Us.
$string['frontpageaboutus'] = 'Strona główna O nas';
$string['frontpageaboutusdesc'] = 'Sekcja O nas na stronie głównej ';
$string['frontpageaboutustitledesc'] = 'Dodaj tytuł do sekcji O nas';
$string['frontpageaboutusbody'] = 'Opis dla sekcji o Nas';
$string['frontpageaboutusbodydesc'] = 'Krótki opis tej sekcji';
$string['enablesectionbutton'] = 'Włącz przyciski na sekcjach';
$string['enablesectionbuttondesc'] = 'Włącz przyciski w głównej sekcji.';
$string['sectionbuttontextdesc'] = 'Wpisz tekst na przycisku w tej sekcji.';
$string['sectionbuttonlinkdesc'] = 'Wpisz URL dla linka w tej sekcji.';
$string['frontpageblocksectiondesc'] = 'Dodaj tytuł do tej sekcji.';

// Block section 1.
$string['frontpageblocksection1'] = 'Tytuł dla pierwszej sekcji';
$string['frontpageblockdescriptionsection1'] = 'Opis dla pierwszej sekcji';
$string['frontpageblockiconsection1'] = 'Ikona Font-Awesome dla pierwszej sekcji';
$string['sectionbuttontext1'] = 'Tekst na przycisku w pierwszej sekcji';
$string['sectionbuttonlink1'] = 'URL linka dla pierwszej sekcji';

// Block section 2.
$string['frontpageblocksection2'] = 'Tytuł dla drugiej sekcji';
$string['frontpageblockdescriptionsection2'] = 'Opis dla drugiej sekcji';
$string['frontpageblockiconsection2'] = 'Ikona Font-Awesome dla drugiej sekcji';
$string['sectionbuttontext2'] = 'Tekst na przycisku w drugiej sekcji';
$string['sectionbuttonlink2'] = 'URL linka dla drugiej sekcji';

// Block section 3.
$string['frontpageblocksection3'] = 'Tytuł dla trzeciej sekcji';
$string['frontpageblockdescriptionsection3'] = 'Opis dla trzeciej sekcji';
$string['frontpageblockiconsection3'] = 'Ikona Font-Awesome dla trzeciej sekcji';
$string['sectionbuttontext3'] = 'Tekst na przycisku w trzeciej sekcji';
$string['sectionbuttonlink3'] = 'URL linka dla trzeciej sekcji';

// Block section 4.
$string['frontpageblocksection4'] = 'Tytuł dla czwartej sekcji';
$string['frontpageblockdescriptionsection4'] = 'Opis dla czwartej sekcji';
$string['frontpageblockiconsection4'] = 'Ikona Font-Awesome dla czwartej sekcji';
$string['sectionbuttontext4'] = 'Tekst na przycisku w czwartej sekcji';
$string['sectionbuttonlink4'] = 'URL linka dla czwartej sekcji';
$string['defaultdescriptionsection'] = 'Standardowy opis ';
$string['frontpageaboutus'] = 'Strona główna O nas';
$string['frontpageaboutusdesc'] = 'Sekcja O nas na stronie głównej ';
$string['enablefrontpageaboutus'] = 'Włącz sekcję O nas';
$string['enablefrontpageaboutusdesc'] = 'Włącz sekcję O nas na stronie głównej.';
$string['frontpageaboutusheading'] = 'Nagłówek O nas';
$string['frontpageaboutusheadingdesc'] = 'Tekst nagłówka tej sekcji';
$string['frontpageaboutustext'] = 'Tekst O nas';
$string['frontpageaboutustextdesc'] = 'Wpisz tekst O nas na stronie głównej.';
$string['frontpageaboutusdefault'] = '<p class="lead">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
              Ut enim ad minim veniam.</p>';
$string['testimonialcount'] = 'Ilość opini';
$string['testimonialcountdesc'] = 'Ilość opini do wyświetlenia';
$string['testimonialimage'] = 'Obrazek opini';
$string['testimonialimagedesc'] = 'Obrazek osoby w opini';
$string['testimonialname'] = 'Imię osoby';
$string['testimonialnamedesc'] = 'Imię osoby';
$string['testimonialdesignation'] = 'Oznaczenia osoby';
$string['testimonialdesignationdesc'] = 'Oznaczenia osoby';
$string['testimonialtext'] = 'Opinie użytkowników';
$string['testimonialtextdesc'] = 'Co powiedzieli inni';
$string['frontpageblockimage'] = 'Wgraj obrazek';
$string['frontpageblockimagedesc'] = 'Możesz wgrać obrazek jako zawartość.';
$string['frontpageblockiconsectiondesc'] = 'Możesz wybrać dowolną ikonę z tej <a href="https://fontawesome.com/v4.7.0/cheatsheet/" target="_new">listy</a>. Wpisz tekst po "fa-". ';
$string['frontpageblockdescriptionsectiondesc'] = 'Krótki opis tytułu.';

// Footer Page Settings.
$string['footersettings'] = 'Ustawienia stopki';
$string['socialmedia'] = 'Ustawienia Social Media';
$string['socialmediadesc'] = 'Podaj linki do social mediów Twojej witryny';
$string['facebooksetting'] = 'Facebook';
$string['facebooksettingdesc'] = 'Podaj link do strony na Facebooku, np. https://www.facebook.com/pagename';
$string['twittersetting'] = 'Twitter';
$string['twittersettingdesc'] = 'Podaj link do strony na Twitterze, np. https://www.twitter.com/pagename';
$string['linkedinsetting'] = 'Linkedin';
$string['linkedinsettingdesc'] = 'Podaj link do strony na Linkedin, np. https://www.linkedin.com/in/pagename';
$string['gplussetting'] = 'Google Plus';
$string['gplussettingdesc'] = 'Podaj link do strony na Google Plus, np. https://plus.google.com/pagename';
$string['youtubesetting'] = 'YouTube';
$string['youtubesettingdesc'] = 'Podaj link do strony na Youtibe, np. https://www.youtube.com/channel/UCU1u6QtAAPJrV0v0_c2EISA';
$string['instagramsetting'] = 'Instagram';
$string['instagramsettingdesc'] = 'Podaj link do strony na Instagramie, np. https://www.instagram.com/name';
$string['pinterestsetting'] = 'Pinterest';
$string['pinterestsettingdesc'] = 'Podaj link do strony na Pinterest, np. https://www.pinterest.com/name';
$string['quorasetting'] = 'quora';
$string['quorasettingdesc'] = 'Podaj link do strony na quora, np. https://www.quora.com/name';
$string['footerbottomtext'] = 'Tekst w lewej dolnej części stopki';
$string['footerbottomlink'] = 'Link w lewej dolnej częsci stopki';
$string['footerbottomlinkdesc'] = 'Wpisz link w dolej lewej części stopki. Np. http://www.TwojaFirma.pl';
$string['footercolumn1heading'] = 'Zawartość pierwszej kolumny stopki (lewa strona)';
$string['footercolumn1headingdesc'] = 'Ta sekcja odnosi się do dolnej części Twojej strony głównej (1. kolumna)';
$string['footercolumn1title'] = 'Tytuł pierwszej kolumny stopki';
$string['footercolumn1titledesc'] = 'Dodaj tytuł do tej kolumny.';
$string['footercolumn1customhtml'] = 'Kod HTML';
$string['footercolumn1customhtmldesc'] = 'Możesz dostosować treść tej kolumny korzystając z HTML.';
$string['footercolumn2heading'] = 'Zawartość drugiej kolumny stopki (środkowa część)';
$string['footercolumn2headingdesc'] = 'Ta sekcja odnosi się do dolnej części Twojej strony głównej (2. kolumna)';
$string['footercolumn2title'] = 'Tytuł drugiej kolumny stopki';
$string['footercolumn2titledesc'] = 'Dodaj tytuł do tej kolumny.';
$string['footercolumn2customhtml'] = 'Kod HTML';
$string['footercolumn2customhtmldesc'] = 'Możesz dostosować treść tej kolumny korzystając z HTML.';
$string['footercolumn3heading'] = 'Zawartość pierwszej kolumny stopki (prawa strona)';
$string['footercolumn3headingdesc'] = 'Ta sekcja odnosi się do dolnej części Twojej strony głównej (3. kolumna)';
$string['footercolumn3title'] = 'Tytuł trzeciej kolumny stopki';
$string['footercolumn3titledesc'] = 'Dodaj tytuł do tej kolumny.';
$string['footercolumn3customhtml'] = 'Kod HTML';
$string['footercolumn3customhtmldesc'] = 'Możesz dostosować treść tej kolumny korzystając z HTML.';
$string['footerbottomheading'] = 'Ustawienia dolnej części stopki';
$string['footerbottomdesc'] = 'Tutaj możesz podać własny link, jaki chcesz, żeby był widoczny w dolnej części stopki';
$string['footerbottomtextdesc'] = 'Dodaj tekst do dolnej części stopki';
$string['poweredbyedwiser'] = 'Powered by Edwiser';
$string['poweredbyedwiserdesc'] = "Odnzacz, aby usunąć \'Powered by Edwiser\' ze swojej strony.";

// Login Page Settings.
$string['loginsettings'] = 'Ustawienia strony logowania';
$string['navlogin_popup'] = 'Włącz okno popup do logowania';
$string['navlogin_popupdesc'] = 'Włącz okno popup do logowania w nagłówku.';
$string['loginsettingpic'] = 'Wgraj obrazek w tle';
$string['loginsettingpicdesc'] = 'Wgraj grafikę, która będzie tłem okna logowania.';
$string['signuptextcolor'] = 'Zarejestruj kolor Color.';
$string['signuptextcolordesc'] = 'Wybierz kolor tekstowy, aby zarejestrować opis strony.';
$string['left'] = "Lewa strona";
$string['right'] = "Prawa strona";
$string['remember_me'] = "Zapamiętaj mnie";
$string['brandlogopos'] = "Pozycja logotypu";
$string['brandlogoposdesc'] = "Jeśli aktywne, logotyp będzie widoczny po prawje stronie panelu bocznego, powyżej formularza logowania.";
$string['brandlogotext'] = "Opis strony";
$string['brandlogotextdesc'] = "Dodaj tekst opisu witryny, który będzie wyświetlany na stronie logowania i rejestracji. Pozostaw to pole puste, jeśli nie chcesz wstawiać żadnego opisu.";
$string['loginpagelayout'] = 'Układ strony logowania';
$string['loginpagelayoutdesc'] = '';
$string['centerlayout'] = 'Wyśrodkowany układ';
$string['overlaylayout'] = 'Układ prawej nakładki';

// License Settings.
$string['licensenotactive'] = '<strong>Uwaga!</strong> Licencja nie jest aktywna, proszę <strong>aktywować</strong> licencję w ustawieniach RemUI.';
$string['licensenotactiveadmin'] = '<strong>Uwaga!</strong> Licencja nie jest aktywna, proszę <strong>aktywować</strong> licencję <a href="'.$CFG->wwwroot.'/admin/settings.php?section=themesettingremui#remuilicensestatus" >tutaj</a>.';
$string['activatelicense'] = 'Aktywuj licencję';
$string['deactivatelicense'] = 'Dezaktywuj licencję';
$string['renewlicense'] = 'Odnów licencję';
$string['deactivated'] = 'Dezaktywowano';
$string['active'] = 'Aktywna';
$string['notactive'] = 'Nieaktywna';
$string['expired'] = 'Wygasła';
$string['licensekey'] = 'Klucz licencyjny';
$string['licensestatus'] = 'Status licencji';
$string['no_activations_left'] = 'Limit przekroczony';
$string['activationfailed'] = 'Aktywacja klucza licencyjnego nie powiodła się. Spróbuj ponownie później.';
$string['noresponsereceived'] = 'Brak odpowiedzi z serwera. Proszę spróbować później.';
$string['licensekeydeactivated'] = 'Klucz licencyjny został dezaktywowany.';
$string['siteinactive'] = 'Strona nieaktywna (Naciśnij Aktywuj licencję w cely aktywowania wtyczki).';
$string['entervalidlicensekey'] = 'Proszę podać ważny klucz licencyjny.';
$string['licensekeyisdisabled'] = 'Twój klucz licencyjny jest wyłączony.';
$string['licensekeyhasexpired'] = "Twoja licencja wygasła. Prosimy o odnowienie.";
$string['licensekeyactivated'] = "Twój klucz licencyjny został aktywowany.";
$string['enterlicensekey'] = "Podaj poprawny klucz licencyjny.";
$string['edwiserremuilicenseactivation'] = 'Aktywacja licencji Edwiser RemUI';
$string['nolicenselimitleft'] = 'Osiągnięto maksymalny limit aktywacji, nie aktywacje pozostały.';

// News And Updates Page.
$string['newsandupdates'] = 'Aktualności i Aktualizacje';
$string['newupdatemessage'] = 'Dostępna jest nowa aktualizacja dla RemUI. <a class="text-white" href="{$a}"><u>Kliknij tutaj</u></a> aby zobaczyć.';
$string['currentversionmessage'] = 'Aktualna wersja to: ';
$string['downloadupdate'] = 'Pobierz aktualizację';
$string['latestversionmessage'] = 'Korzystasz z najnowszej wersji RemUI.';
$string['rateremui'] = 'Oceń RemUI';
$string['fullname']  = 'Pełna nazwa';
$string['providefeedback'] = 'Przekaż nam opinię o RemUI';
$string['sendfeedback'] = 'Wyślij opinię';
$string['recentnews'] = 'Ostatnie aktualności';

// About Edwiser RemUI.
$string['aboutsettings'] = 'Informacje o Edwiser RemUI';
$string['notenrolledanycourse'] = 'Niezapisany do żadnego kursu';

// My Course Page.
$string['resume'] = 'Wznów';
$string['start'] = 'Start';
$string['completed'] = 'Ukończony';

// Course.
$string['graderreport'] = 'Raport ocen';
$string['enroluser'] = 'Zapisz użytkowników';
$string['activityeport'] = 'Raport aktywności';
$string['editcourse'] = 'Edytuj kurs';
$string['sections'] = "Sekcje";

// Next Previous Activity.
$string['activityprev'] = 'Poprzednia aktywność';
$string['activitynext'] = 'Następna aktywność';

// Login Page.
$string['signin'] = 'Zaloguj';
$string['signup'] = 'Zapisz się';
$string['noaccount'] = 'Nie masz konta?';

// Incourse Page.
$string['backtocourse'] = 'Przegląd kursu';

// Header Section.
$string['togglefullscreen'] = 'Przełącz na tryb pełnoekranowy';
$string['recent'] = 'Niedawny';

// Course Stats.
$string['enrolledusers'] = 'Zarejestrowani <br>Studenci';
$string['studentcompleted'] = 'Studenci <br>ukończeni';
$string['inprogress'] = 'W <br>trakcie';
$string['yettostart'] = 'Jeszcze <br>na start';

// Footer Content.
$string['followus'] = 'Obserwuj nas';
$string['poweredby'] = 'Wspierane przez Edwiser RemUI';

// Course Archive Page.
$string['mycourses'] = "Moje kursy";
$string['allcategories'] = 'Wszystkie kategorie';
$string['categorysort'] = 'Sortuj kategorie';
$string['sortdefault'] = 'Sortuj';
$string['sortascending'] = 'Sortuj od A do Z';
$string['sortdescending'] = 'Sortuj od Z do A';

// Dashboard Blocks.
$string['viewcourse'] = "ZOBACZ KURS";
$string['searchcourses'] = "Szukaj kursów";

$string['hiddencourse'] = 'Ukryty kurs';

// Usage tracking.
$string['enableusagetracking'] = "Włącz śledzenie użycia";

$string['enableusagetrackingdesc'] = "<strong>UWAGA ŚLEDZENIA UŻYTKOWANIA</strong>

<hr class='text-muted' />

<p>Od tej pory Edwiser będzie zbierać anonimowe dane w celu generowania statystyk użytkowania produktu.</p>

<p>Te informacje pomogą nam poprowadzić rozwój we właściwym kierunku i zapewnić sukces społeczności Edwiser.</p>

<p>Powiedziawszy, że nie zbieramy twoich danych osobowych ani twoich studentów podczas tego procesu. Możesz wyłączyć to we wtyczce, ilekroć chcesz zrezygnować z tej usługi.</p>

<p>Przegląd zebranych danych jest dostępny <strong><a href='https://forums.edwiser.org/topic/67/anonymously-tracking-the-usage-of-edwiser-products' target='_blank'>tutaj</a></strong>.</p>";

$string['focusmodesettings'] = 'Focus Mode Settings';
$string['enablefocusmode'] = 'Enable Focus Mode';
$string['enablefocusmodedesc'] = 'Enabling this setting will open the course and activity page such a way so that students will not lose focus of main Course content';
$string['focusmodeenabled'] = 'Tryb ostrości włączony';
$string['focusmodedisabled'] = 'Tryb ostrości wyłączony';
$string['coursedata'] = 'Dane kursu';

$string['prev'] = 'Poprzedni';
$string['next'] = 'Kolejny';

// RemUI one-click update.
$string['errors'] = 'Błędy';
$string['invalidzip'] = 'Nieprawidłowy plik zip. <b>{$a}</b>';
$string['errorfetching'] = 'Błąd podczas pobierania ZIP wtyczki. <b>{$a}</b>';
$string['errorfetchingexist'] = 'Błąd podczas pobierania ZIP wtyczki: istnieje lokalizacja docelowa. <b>{$a}</b>';
$string['unabletounzip'] = 'Nie można rozpakować <b>{$a}</b>';
$string['unabletoloadplugindetails'] = 'Nie można załadować szczegółów wtyczki <b>{$a}</b>';
$string['requirehigherversion'] = 'Wymaga wersji Moodle: <b>{$a}</b>';
$string['noupdates'] = 'Wszystko jest aktualne.';
$string['invalidjsonfile'] = 'Błąd: nieprawidłowy plik JSON listy produktów Edwiser.';
$string['recommendation'] = 'Polecane wtyczki';
$string['comeswith'] = 'Pochodzi z: {$a}';
$string['changelog'] = 'Changelog';
$string['currentrelease'] = 'Bieżąca wersja: {$a}';
$string['updateavailable'] = 'Dostępna aktualizacja: {$a}';
$string['uptodate'] = 'Aktualny';

// Information center.
$string['informationcenter'] = 'Centrum Informacji';

$string['nocoursefound'] = 'Nie znaleziono kursu';

$string['badges'] = 'Odznaki';

// Course Page Settings.
$string['coursesettings'] = "Ustawienia strony kursu";
$string['enrolpagesettings'] = "Ustawienia strony rejestracji";
$string['enrolpagesettingsdesc'] = "Tutaj możesz zarządzać zawartością strony rejestracji.";
$string['coursearchivepagesettings'] = 'Ustawienia strony archiwum kursów';
$string['coursearchivepagesettingsdesc'] = 'Zarządzaj układem i zawartością strony archiwum kursu.';

$string['enrolment_payment'] = 'Opłata za kurs';
$string['enrolment_payment_desc'] = 'Ustawienia preferencji rejestracji na kurs. Czy wszystkie kursy są płatne, czy niektóre są bezpłatne? To ustawienie określa, jak będzie działać rejestracja na kurs i jak będzie wyświetlane.';
$string['allrequirepayment'] = 'Wszystkie kursy są płatne';
$string['somearefree'] = 'Niektóre kursy są bezpłatne';
$string['allarefree'] = 'Wszystkie kursy są bezpłatne';

$string['showcoursepricing'] = 'Pokaż ceny kursów';
$string['showcoursepricingdesc'] = 'Włącz to ustawienie, aby wyświetlić sekcję cen na stronie rejestracji.';
$string['fullwidthcourseheader'] = 'Nagłówek pola o pełnej szerokości';
$string['fullwidthcourseheaderdesc'] = 'Włącz to ustawienie, aby nagłówek kursu miał pełną szerokość.';

$string['price'] = 'Cena';
$string['course_free'] = 'FREE';
$string['enrolnow'] = 'Zarejestruj się teraz';
$string['buyand'] = 'Kup & ';
$string['notags'] = 'Brak tagów.';
$string['tags'] = 'Tagi';

$string['enrolment_layout'] = 'Układ strony rejestracji';
$string['enrolment_layout_desc'] = 'Włącz nowy projekt układu strony rejestracji';
$string['disable'] = 'Wyłączyć';
$string['enable_layout1'] = 'Włącz układ 1';

$string['webpage'] = "Strona internetowa";
$string['categorypagelayout'] = 'Układ strony kategorii';
$string['categorypagelayoutdesc'] = 'Wybór projektu strony archiwum kursu';
$string['layout'] = 'Układ';
$string['categoryfilter'] = 'Filtr kategorii';

$string['skill1'] = 'Początkujący';
$string['skill2'] = 'Pośredni';
$string['skill3'] = 'zaawansowane';

$string['lastupdatedon'] = 'Ostatnia aktualizacja ';

// Plural and Singular.
$string['hourcourse'] = ' Kurs godzinny';
$string['hourscourse'] = ' Kurs godzinowy';
$string['enrolledstudent'] = ' Zapisany student';
$string['enrolledstudents'] = ' Zapisani studenci';
$string['downloadresource'] = ' Zasoby do pobrania';
$string['assignment'] = ' Zadanie';
$string['strcourse'] = ' Kierunek';
$string['strcourses'] = ' Kursy';
$string['strstudent'] = ' Student';
$string['strstudents'] = ' Studenci';
$string['showenrolledcourses'] = 'Pokaż zarejestrowane kursy';
$string['categoryselectionrequired'] = 'Wymagany wybór kategorii.';
$string['courseoverview'] = 'Przegląd kursu';
$string['coursecontent'] = 'Zawartość kursu';
$string['startdate'] = 'Data rozpoczęcia';
$string['category'] = 'Kategoria';
$string['aboutinstructor'] = "O instruktorze";
$string['showmore'] = "Pokaż więcej";
$string['coursefeatures'] = "Funkcje kursu";

$string['lectures'] = "Wykłady";
$string['quizzes'] = "Quizy";
$string['startdate'] = "Data rozpoczęcia";
$string['skilllevel'] = "Poziom umiejętności";
$string['language'] = "Język";
$string['assessments'] = "Oceny";

// Customizer strings.
$string['customizer-migrate-notice'] = 'Ustawienia kolorów są migrowane do Customizer. Kliknij poniższy przycisk, aby otworzyć konfigurator.';
$string['customizer-close-heading'] = 'Zamknij konfigurator';
$string['customizer-close-description'] = 'Niezapisane zmiany zostaną odrzucone. Czy chciałbyś kontynuować?';
$string['reset-settings'] = 'Zresetuj wszystkie ustawienia dostosowywania';
$string['reset-settings-description'] = 'Ustawienia dostosowywania zostaną przywrócone do wartości domyślnych. Wszystkie ustawienia z nowej instalacji zostaną wybrane jako domyślne. Czy chcesz kontynuować?';
$string['customizer'] = 'Customizer';
$string['error'] = 'Błąd';
$string['resetdesc'] = 'Zresetuj ustawienie do ostatniego zapisania lub ustawienia domyślnego, gdy nic nie zostało zapisane';
$string['noaccessright'] = 'Przepraszam! Nie masz praw do korzystania z tej strony';
$string['font-family'] = 'Rodzina czcionek';
$string['font-family_help'] = 'Ustaw rodzinę czcionek na {$a}';
$string['font-size'] = 'Rozmiar czcionki';
$string['font-size_help'] = 'Ustaw rozmiar czcionki na {$a}';
$string['font-weight'] = 'Grubość czcionki';
$string['font-weight_help'] = 'Ustaw grubość czcionki na {$a}. Właściwość font-weight określa, jak grube lub cienkie znaki w tekście powinny być wyświetlane.';
$string['line-height'] = 'Wysokość linii';
$string['line-height_help'] = 'Ustaw wysokość wiersza {$a}';
$string['global'] = 'Światowy';
$string['global_help'] = 'Możesz zarządzać globalnymi ustawieniami, takimi jak kolor, czcionka, nagłówek, przyciski itp.';
$string['site'] = 'Teren';
$string['text-color'] = 'Kolor tekstu';
$string['text-color_help'] = 'Ustaw kolor tekstu na {$a}';
$string['text-hover-color'] = 'Kolor tekstu po najechaniu kursorem';
$string['text-hover-color_help'] = 'Ustaw kolor tekstu po najechaniu na {$a}';
$string['link-color'] = 'Kolor linków';
$string['link-color_help'] = 'Ustaw kolor linku na {$a}';
$string['link-hover-color'] = 'Kolor linku po najechaniu myszą';
$string['link-hover-color_help'] = 'Ustaw kolor wskaźnika myszy na {$a}';
$string['typography'] = 'Typografia';
$string['inherit'] = 'Dziedziczyć';
$string["weight-100"] = 'Cienki 100';
$string["weight-200"] = 'Extra-Light 200';
$string["weight-300"] = 'Światło 300';
$string["weight-400"] = 'Normalny 400';
$string["weight-500"] = 'Średnia 500';
$string["weight-600"] = 'Półgrubość 600';
$string["weight-700"] = 'Pogrubienie 700';
$string["weight-800"] = 'Extra-Bold 800';
$string["weight-900"] = 'Ultra-Bold 900';
$string['text-transform'] = 'Przekształcenie tekstu';
$string['text-transform_help'] = 'Właściwość text-transform kontroluje wielkość liter w tekście. Ustaw przekształcenie tekstu na {$a}.';
$string["default"] = 'Domyślna';
$string["none"] = 'Żaden';
$string["capitalize"] = 'Skapitalizować';
$string["uppercase"] = 'Duże litery';
$string["lowercase"] = 'Małe litery';
$string['background-color'] = 'Kolor tła';
$string['background-color_help'] = 'Ustaw kolor tła na {$a}';
$string['background-hover-color'] = 'Kolor tła po najechaniu kursorem';
$string['background-hover-color_help'] = 'Ustaw kolor tła najechania kursorem na {$a}';
$string['color'] = 'Kolor';
$string['customizing'] = 'Dostosowywanie';
$string['savesuccess'] = 'Zapisano pomyślnie.';
$string['mobile'] = 'mobilny';
$string['tablet'] = 'Tablet';
$string['hide-customizer'] = 'Ukryj konfigurator';
$string['customcss_help'] = 'Możesz dodać niestandardowy CSS. Zostanie to zastosowane na wszystkich stronach Twojej witryny.';

// Customizer Global body.
$string['body'] = 'Ciało';
$string['body-font-family_desc'] = 'Ustaw rodzinę czcionek dla całej witryny.';
$string['body-font-size_desc'] = 'Ustaw podstawowy rozmiar czcionki dla całej witryny.';
$string['body-fontweight_desc'] = 'Ustaw grubość czcionki dla całej witryny.';
$string['body-text-transform_desc'] = 'Ustaw transformację tekstu dla całej witryny.';
$string['body-lineheight_desc'] = 'Ustaw wysokość linii dla całej witryny.';
$string['faviconurl_help'] = 'URL Favicon';

// Customizer Global heading.
$string['heading'] = 'Heading';
$string['use-custom-color'] = 'Użyj koloru niestandardowego';
$string['use-custom-color_help'] = 'Użyj niestandardowego koloru dla {$a}';
$string['typography-heading-all-heading'] = 'Headings (H1 - H6)';
$string['typography-heading-h1-heading'] = 'Heading 1';
$string['typography-heading-h2-heading'] = 'Heading 2';
$string['typography-heading-h3-heading'] = 'Heading 3';
$string['typography-heading-h4-heading'] = 'Heading 4';
$string['typography-heading-h5-heading'] = 'Heading 5';
$string['typography-heading-h6-heading'] = 'Heading 6';

// Customizer Colors.
$string['primary-color'] = 'Kolor podstawowy';
$string['primary-color_help'] = 'Zastosuj kolor podstawowy do całej witryny. Ten kolor zostanie zastosowany do marki nagłówka, głównego przycisku, przełącznika prawej szuflady, górnego przycisku goto itp. Aby go użyć, możesz zastosować bg-primary dla tła i btn-primary dla przycisku.';
$string['page-background'] = 'Tło strony';
$string['page-background_help'] = 'Ustaw niestandardowe tło strony na obszar zawartości strony. Możesz wybrać kolor, gradient lub obraz. Kąt koloru gradientu wynosi 100 stopni.';
$string['page-background-color'] = 'Kolor tła strony';
$string['page-background-color_help'] = 'Ustaw kolor tła na obszar zawartości strony.';
$string['page-background-image'] = 'Obraz tła strony';
$string['page-background-image_help'] = 'Ustaw obraz jako tło dla obszaru zawartości strony.';
$string['gradient'] = 'Gradient';
$string['gradient-color1'] = 'Kolor gradientu 1';
$string['gradient-color1_help'] = 'Ustaw pierwszy kolor gradientu';
$string['gradient-color2'] = 'Kolor gradientu 2';
$string['gradient-color2_help'] = 'Ustaw drugi kolor gradientu';
$string['page-background-imageattachment'] = 'Załącznik obrazu tła';
$string['page-background-imageattachment_help'] = 'Właściwość background-attachments określa, czy obraz tła przewija się wraz z resztą strony, czy też jest ustalony.';
$stirng['image'] = 'Wizerunek';
$string['additional-css'] = 'Dodatkowe css';
$string['left-sidebar'] = 'Lewy pasek boczny';
$string['main-sidebar'] = 'Główny pasek boczny';
$string['sidebar-links'] = 'Linki na pasku bocznym';
$string['secondary-sidebar'] = 'Dodatkowy pasek boczny';
$string['header'] = 'nagłówek';
$string['menu'] = 'Menu';
$string['site-identity'] = 'Tożsamość witryny';
$string['primary-header'] = 'Główny nagłówek';
$string['color'] = 'Kolor';

// Customizer Buttons.
$string['buttons'] = 'guziki';
$string['border'] = 'Granica';
$string['border-width'] = 'Szerokość granicy';
$string['border-width_help'] = 'Ustaw szerokość obramowania {$a}';
$string['border-color'] = 'Kolor ramki';
$string['border-color_help'] = 'Ustaw kolor obramowania {$a}';
$string['border-hover-color'] = 'Kolor obramowania po najechaniu kursorem';
$string['border-hover-color_help'] = 'Ustaw kolor obramowania najechania kursorem na {$a}';
$string['border-radius'] = 'Promień graniczny';
$string['border-radius_help'] = 'Ustaw promień obramowania {$a}';
$string['letter-spacing'] = 'Odstępy między literami';
$string['letter-spacing_help'] = 'Ustaw odstępy między literami na {$a}';
$string['text'] = 'Tekst';
$string['padding'] = 'Wyściółka';
$string['padding-top'] = 'Top z wyściółką';
$string['padding-top_help'] = 'Ustaw dopełnienie u góry {$a}';
$string['padding-right'] = 'Wypełnienie w prawo';
$string['padding-right_help'] = 'Ustaw dopełnienie na prawo od {$a}';
$string['padding-bottom'] = 'Wyściełany dół';
$string['padding-bottom_help'] = 'Ustaw dopełnienie na dole {$a}';
$string['padding-left'] = 'Wypełnienie w lewo';
$string['padding-left_help'] = 'Ustaw dopełnienie na lewo od {$a}';
$string['secondary'] = 'Wtórny';
$string['colors'] = 'Zabarwienie';

// Customizer Header.
$string['header-background-color_help'] = 'Ustaw kolor tła nagłówka. Kolor tła logo marki będzie kolorem podstawowym. Ten kolor zostanie zastosowany do pozycji menu.';
$string['site-logo'] = 'Logo witryny';
$string['header-menu'] = 'Menu nagłówka';
$string['border-bottom-size'] = 'Rozmiar dolnej krawędzi';
$string['border-bottom-size_help'] = 'Ustaw dolny rozmiar obramowania nagłówka witryny';
$string['border-bottom-color'] = 'Kolor dolnej krawędzi';
$string['border-bottom-color_help'] = 'Ustaw kolor dolnego obramowania nagłówka witryny';
$string['layout-desktop'] = 'Układ pulpitu';
$string['layout-desktop_help'] = 'Ustaw układ nagłówka dla pulpitu';
$string['layout-mobile'] = 'Układ mobilny';
$string['layout-mobile_help'] = 'Ustaw układ nagłówka na urządzenia mobilne';
$string['header-left'] = 'Lewa ikona prawe menu';
$string['header-right'] = 'Prawa ikona lewe menu';
$string['header-top'] = 'Górna ikona dolne menu';
$string['hover'] = 'Unosić się';
$string['logo'] = 'Logo';
$string['applynavbarcolor'] = 'Ustaw kolor witryny dla paska nawigacyjnego';
$string['header-background-color-warning'] = 'Nie będzie używany, jeśli opcja <strong> Ustaw kolor paska nawigacyjnego w witrynie </strong> jest włączona.';
$string['applynavbarcolor_help'] = 'Podstawowy kolor witryny zostanie zastosowany do całego nagłówka. Zmiana koloru podstawowego zmieni kolor tła nagłówka. Nadal możesz zastosować niestandardowy kolor tekstu i kolor najechania kursorem do menu nagłówka.';
$string['logosize'] = 'Oczekiwany współczynnik proporcji to 130: 33 dla widoku z lewej strony, 140: 33 dla widoku z prawej strony.';
$string['logominisize'] = 'Oczekiwany współczynnik proporcji to 40:33.';
$string['sitenamewithlogo'] = 'Nazwa witryny z logo (tylko widok z góry)';

// Customizer Sidebar.
$string['link-text'] = 'tekst linku';
$string['link-text_help'] = 'Ustaw kolor tekstu linku na {$a}';
$string['link-icon'] = 'Ikona łącza';
$string['link-icon_help'] = 'Ustaw kolor ikony linku na {$a}';
$string['active-link-color'] = 'Kolor aktywnego łącza';
$string['active-link-color_help'] = 'Ustaw niestandardowy kolor dla aktywnego linku {$a}';
$string['active-link-background'] = 'Tło aktywnego łącza';
$string['active-link-background_help'] = 'Ustaw niestandardowy kolor na tło aktywnego linku {$a}';
$string['link-hover-background'] = 'Tło w dymku z linkiem';
$string['link-hover-background_help'] = 'Ustaw tło wskaźnika myszy na {$a}';
$string['link-hover-text'] = 'Tekst w dymku z linkiem';
$string['link-hover-text_help'] = 'Ustaw kolor tekstu po najechaniu linkiem na {$a}';
$string['hide-dashboard'] = 'Ukryj pulpit nawigacyjny';
$string['hide-dashboard_help'] = 'Włączenie tej opcji spowoduje ukrycie elementu Dashboard na pasku bocznym';
$string['hide-home'] = 'Ukryj dom';
$string['hide-home_help'] = 'Włączenie tej opcji spowoduje ukrycie elementu głównego na pasku bocznym';
$string['hide-calendar'] = 'Ukryj kalendarz';
$string['hide-calendar_help'] = 'Włączenie tej opcji spowoduje ukrycie elementu Kalendarza na pasku bocznym';
$string['hide-private-files'] = 'Ukryj pliki prywatne';
$string['hide-private-files_help'] = 'Włączając tę ​​opcję, element Pliki prywatne z paska bocznego zostanie ukryty';
$string['hide-my-courses'] = 'Ukryj moje kursy';
$string['hide-my-courses_help'] = 'Po włączeniu tej opcji Moje kursy i zagnieżdżone elementy kursów z paska bocznego zostaną ukryte';
$string['hide-content-bank'] = 'Ukryj bank zawartości';
$string['hide-content-bank_help'] = 'Włączając tę ​​opcję, element banku zawartości z paska bocznego zostanie ukryty';

// Customizer Footer.
$string['footer'] = 'Stopka';
$string['basic'] = 'Podstawowy';
$string['advance'] = 'Postęp';
$string['footercolumn'] = 'Widget.';
$string['footercolumndesc'] = 'Liczba widżetów do pokazania w stopce.';
$string['footercolumntype'] = 'Rodzaj';
$string['footercolumntypedesc'] = 'Możesz wybrać typ widgetu stopki';
$string['footercolumnsocial'] = 'Linki mediów społecznościowych.';
$string['footercolumnsocialdesc'] = 'Pokaż selektywne łącza mediów społecznościowych';
$string['footercolumntitle'] = 'Tytuł';
$string['footercolumntitledesc'] = 'Dodaj tytuł do tego widżetu.';
$string['footercolumncustomhtml'] = 'Zawartość';
$string['footercolumncustomhtmldesc'] = 'Możesz dostosować zawartość tej szerokości przy użyciu poniżej danego edytora.';
$string['both'] = 'Obie';
$string['footercolumnsize'] = 'Rozmiar widżetu.';
$string['footercolumnsizenote'] = 'Przeciągnij pionową linię, aby dostosować rozmiar widżetu.';
$string['footercolumnsizedesc'] = 'Możesz ustawić indywidualny rozmiar widżetu.';
$string['footercolumnmenu'] = 'menu';
$string['footercolumnmenudesc'] = 'Menu Link.';
$string['footermenu'] = 'menu';
$string['footermenudesc'] = 'Dodaj menu w widżecie stopki.';
$string['customizermenuadd'] = 'Dodaj pozycję menu.';
$string['customizermenuedit'] = 'Edytuj element menu.';
$string['customizermenumoveup'] = 'Przenieś element menu';
$string['customizermenuemovedown'] = 'Przenieś element menu w dół';
$string['customizermenuedelete'] = 'Usuń element menu.';
$string['menutext'] = 'Tekst';
$string['menuaddress'] = 'Adres';
$string['menuorientation'] = 'Orientacja menu.';
$string['menuorientationdesc'] = 'Ustaw orientację menu.Orientacja może być pionowa lub pozioma.';
$string['menuorientationvertical'] = 'Pionowy';
$string['menuorientationhorizontal'] = 'Poziomy';
$string['footershowlogo'] = 'Logo';
$string['footershowlogodesc'] = 'Pokaż logo w stopce wtórnym.';
$string['footersecondarysocial'] = 'Linki mediów społecznościowych';
$string['footersecondarysocialdesc'] = 'Pokaż łącza mediów społecznościowych w stopce wtórnej.';
$string['footertermsandconditionsshow'] = 'Pokaż warunki';
$string['footertermsandconditions'] = 'Zasady i Warunki';
$string['footertermsandconditionsdesc'] = 'Możesz dodać link do strony.';
$string['footerprivacypolicyshow'] = 'Pokaż politykę prywatności';
$string['footerprivacypolicy'] = 'Link Polityka prywatności.';
$string['footerprivacypolicydesc'] = 'Możesz dodać link do strony polityki prywatności.';
$string['footercopyrightsshow'] = 'Pokaż treść praw autorskich.';
$string['footercopyrights'] = 'Prawa autorskie';
$string['footercopyrightsdesc'] = 'Dodaj zawartość praw autorskich na dole strony.';
$string['footercopyrightstags'] = 'Tagi:<br>[site]  -  Nazwa strony<br>[year]  -  Rok bieżący';
$string['termsandconditions'] = 'Zasady i Warunki';
$string['privacypolicy'] = 'Polityka prywatności';

// Customizer login.
$string['login'] = 'Zaloguj sie';
$string['panel'] = 'Płyta';
$string['page'] = 'Strona';
$string['loginbackgroundopacity'] = 'Zaloguj się tła nieprzezroczystość.';
$string['loginbackgroundopacity_help'] = 'Zastosuj krycie do logowania obrazu tła.';
$string['loginpanelbackgroundcolor_help'] = 'Zastosuj kolor tła do panelu logowania.';
$string['loginpaneltextcolor_help'] = 'Zastosuj kolor tekstowy do panelu logowania.';
$string['loginpanellinkcolor_help'] = 'Zastosuj kolor łącza do panelu logowania.';
$string['loginpanellinkhovercolor_help'] = 'Zastosuj link kolorowy kolor do panelu logowania.';
$string['login-panel-position'] = 'Pozycja panelu logowania';
$string['login-panel-position_help'] = 'Ustaw pozycję do panelu logowania i rejestracji';
$string['login-panel-logo-default'] = 'Logo nagłówka';
$string['login-panel-logo-desc'] = 'Zależy od <strong>Wybierz ustawienie formatu logo witryny</strong>';
$string['login-page-info'] = 'Strona logowania nie może być wyświetlona w dostosowywaniu, ponieważ może być wyświetlany tylko przez wylogowany użytkownika.
Możesz przetestować ustawienie, zapisując i otwierając stronę logowania w trybie incognito.';

// One click report  bug/feedback.
$string['sendfeedback'] = "Wyślij opinię do Edwisera";
$string['descriptionmodal_text1'] = "<p>Opinie pozwala wysłać nam sugestie dotyczące naszych produktów. Witamy raporty problemowe, Posiada pomysły i ogólne komentarze.</p><p>Zacznij od piszenia krótkiego opisu:</p>";
$string['descriptionmodal_text2'] = "<p>Następnie pozwolimy Ci zidentyfikować obszary strony związanej z opisem.</p>";
$string['emptydescription_error'] = "Wprowadź opis.";
$string['incorrectemail_error'] = "Wprowadź odpowiedni identyfikator e-mail.";

$string['highlightmodal_text1'] = "Kliknij i przeciągnij na stronie, aby pomóc nam lepiej zrozumieć swoją opinię.Możesz przenieść ten okno dialogowe, jeśli jest na drodze.";
$string['highlight_button'] = "Podświetlaj obszar";
$string['blackout_button'] = "Ukryj informacje";
$string['highlight_button_tooltip'] = "Podświetl obszary istotne dla Twojej opinii.";
$string['blackout_button_tooltip'] = "Ukryj wszelkie dane osobowe.";

$string['feedbackmodal_next'] = 'Weź zrzut ekranu i kontynuuj';
$string['feedbackmodal_skipnext'] = 'Pomiń i kontynuuj';
$string['feedbackmodal_previous'] = 'Z powrotem';
$string['feedbackmodal_submit'] = 'Zatwierdź';
$string['feedbackmodal_ok'] = 'w porządku';

$string['description_heading'] = 'Opis';
$string['feedback_email_heading'] = 'E-mail';
$string['additional_info'] = 'dodatkowe informacje';
$string['additional_info_none'] = 'Żaden';
$string['additional_info_browser'] = 'Informacje o przeglądarce.';
$string['additional_info_page'] = 'Informacja o stronie';
$string['additional_info_pagestructure'] = 'Struktura strony';
$string['feedback_screenshot'] = 'Zrzut ekranu';
$string['feebdack_datacollected_desc'] = 'Przegląd gromadzonych danych jest dostępny <strong><a href="https://forums.edwiser.org/topic/67/anonymously-tracking-the-usage-of-edwiser-products" target="_blank">tutaj</a></strong>.';

$string['submit_loading'] = 'Ładowanie...';
$string['submit_success'] = 'Dziękujemy za twoją opinię.Cenimy każdą informację zwrotną, którą otrzymujemy.';
$string['submit_error'] = 'Niestety pojawił błąd podczas wysyłania opinii.Proszę spróbuj ponownie.';
$string['send_feedback_license_error'] = "Aktywuj licencję, aby uzyskać obsługę produktu.";

// Setup wizard.
$string['setupwizard'] = "Kreator konfiguracji";
$string['general'] = "General";
$string['coursepage'] = "Strona kursu";
$string['pagelayout'] = "Układ strony";
$string['loginpage'] = "Strona logowania";
$string['skipsetupwizard'] = "Pomiń kreatora konfiguracji";
$string['setupwizardmodalmsg'] = "O krok od korzystania z Edwiser RemUI,Kliknij Kreator instalacji, aby dostosować motyw, \"Anuluj\", aby użyć ustawień domyślnych.";
$string["alert"] = "Alert";
$string["success"] = "Sukces";
$string['coursesection'] = "Zawartość kursu";
$string['coursespecificlinks'] = "Nawigacja po kursie";
$string['universallinks'] = 'Nawigacja w witrynie';

// Importer.
$string['importer'] = 'Importer';
$string['showimporter'] = 'Pokaż importer';
$string['showimporterdesc'] = 'Pokaż ikonę importera w lewym stopie boku.';
$string['importer-missing'] = 'Edwiser Site Importer plugin is missing. Please visit <a href="https://edwiser.org">Edwiser</a> site to download this plugin.';
