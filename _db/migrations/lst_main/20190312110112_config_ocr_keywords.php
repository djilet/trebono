<?php


use Phinx\Migration\AbstractMigration;

class ConfigOcrKeywords extends AbstractMigration
{
    public function up()
    {
        $updated = Connection::GetSQLString(GetCurrentDateTime());
        $this->execute("INSERT INTO config (code, value, group_code, editor, updated)
                                        VALUES ('ocr_keywords',
                                        'sum
eur
mastercard
visa
kundenbeleg
chef
theke
aal
absinth
acai
acerola
ackerbohnen
afa alge frisch
agavendicksaft
ahornsirup
ajvar
aloe vera saft
altbier
amaranth
ananas
ananassaft
anis
aperol likör
apfel
apfeldicksaft
apfelessig
apfelkuchen
apfelmus
apfelringe getrocknet
apfelsaft
apfelschorle
apfelwein
appenzeller
aprikose
aronia beeren
artischocke
aubergine
auster
austernpilze
avocado
ayran
backfisch
backpulver
bacon
baguette
baiser
balsamico creme
balsamico essig
bambussprossen
banane
bar
barsch
basilikum
basilikum pesto
basmati reis roh
bauchspeck
      bäckerei
beifuß getrocknet
berberitzen getrocknet
bergkäse 50% i.tr.
berliner
bier
bier alkoholfrei
bierschinken
birne
bismarckhering
blattsalat
blaukraut
blauschimmelkäse
blumenkohl
blutwurst
blätterteig
bockshornklee
bockwurst
bohnenkraut getrocknet
bonbons
borretsch
brandteig
bratensauce
brathering
bratkartoffeln
bratwurst
brennnessel
brezel
brie
brokkoli
brombeeren
brötchen weizen
buchweizen
buchweizenmehl
bulgur
butter
butterbrezel
butterkekse
butterkäse
buttermilch
butterpilze
bärlauch
büffelmozzarella
bündnerfleisch
cabanossi
camembert
camu-camu pulver
cappuccino
cashewmus
cashewnüsse
cervelatwurst
cevapcici
champagner
champignons
cheddar käse
cheeseburger
cherrytomaten
chia
chia brot
chia brötchen
chicken nuggets
chicken wings
chicorée
chili con carne
chilischote
chinakohl
chlorella
ciabatta brot
cidre
clementine
cola
cola light
cola zero
cordon bleu v. schwein
corned beef
cornflakes natur
couscous gekocht
couscous roh
cranberries getrocknet
cranberry
creme fraiche 15%
creme fraiche 30%
crevetten
croissant
curry
currywurst mit sauce
datteln
dicke bohnen
diesel
dill
dinkelbrot
dinkelbrötchen
dinkelflocken
dinkelkleie
dinkelmehl vollkorn
dinkelnudeln roh
dinkelvollkornbrot
diätbier
dominosteine
donut
dorade
drachenfrucht
döner
dönerfleisch
edamer käse
ei - vollei
eierkuchen
eierlikör (20% vol.)
eierpfannkuchen
eiersalat mit mayonnaise
eierschecke
eigelb
eisbein roh
eisbergsalat
eiscreme
eiscreme
eiskaffee
eistee
eiweiss / eiklar
eiweißbrot
eiweißbrötchen
eiweißpulver
eiweißshake mit milch
emmentaler käse
endiviensalat
energydrink
ente
entenbrust
entenleber
erbsensuppe
erdbeeren
erdbeerjoghurt 1.5%
erdbeerkuchen biskuitteig
erdnussbutter
erdnussflips
erdnussmus
erdnussöl
erdnüsse
erythrit
espresso
essig
essig öl dressing
estragon frisch
estragon getrocknet
falafel
fanta orange
federweißer
feige
feldsalat
fenchel
feta käse
fischstäbchen
fladenbrot
flammkuchen
fleischbrühe
fleischkäse
fleischsalat
fleischwurst
flohsamenschalen
flusskrebs
forelle
forelle geräuchert
franzbrötchen
frikadelle
frischkäse fettreduziert
frischkäse körnig
frischkäse doppelrahm
fruchteis / sorbet
fruchtjoghurt 1.5%
fruchtsaft
fruchtzucker
früchtemüsli
früchtetee
frühlingsrolle
frühlingszwiebel
frühlingszwiebeln
galiamelone
gambas
gans
garnelen
geflügelfleischwurst
geflügelwiener
geflügelwurst
gelatine
gemüsebrühe
gemüsesaft
germknödel
gerstengras pulver
gerstenmehl vollkorn
gewürzgurken
gheebutter
gin (40% vol.)
glasnudeln roh
gluten weizenkleber
glühwein
gnocchi
goji beeren getrocknet
gorgonzola
gouda käse
granatapfel
grapefruit
grapefruitsaft
grappa (40% vol.)
graubrot
graupen
griechischer joghurt 10%
grießbrei
grissinis
grüne bohnen
grüne erbsen
grüner spargel
grüner tee
grünkohl
grützwurst
guacamole
guarkernmehl
guave
gummibärchen
gurke grün
gyros
gänsebrust
gänsekeule
gänseleberpastete
gänseschmalz
götterspeise gekocht
hackfleisch gemischt
haferflocken
haferkleie
hafermilch
hagebutten
hagebuttentee
halbfettmargarine
halloumi
hamburger
handkäse
hanfmehl
hanfsamen
harzer käse
hase
haselnussmilch
haselnussmus
haselnüsse
hefeflocken
hefeteig
hefezopf
heidelbeeren
heilbutt
heiße schokolade
heringsfilet
himbeeren
hirsch
hirse
hirseflocken
holunder
holundersaft
honig
honigmelone
hugo aperitif cocktail
hummer
hummus
hähnchen
hähnchen gebraten
hähnchenbrust
hähnchenleber
hähnchenschenkel
hähnchenschnitzel paniert gebraten
hühnerfrikassee
hühnersuppe
hüttenkäse
ingwer
isomalt
jagdwurst
jakobsmuschel
jasminreis roh
joghurt 01%
joghurt 15%
joghurt 3.5%
joghurtdressing
joghurteis
johannisbrotkernmehl
jägermeister kräuterlikör
kabeljau / dorsch
kaffee komplett
kaffee mit milch
kaffee schwarz
kaffeesahne 10%
kaffeesahne 4%
kaffeesahne 75%
kaisergemüse
kaiserschmarrn
kakao stark entölt
kakaobohnen
kakaobutter
kakaofasern
kakaomilch 15%
kakaomilch 35%
kakaopulver gezuckert
kaki
kalbsbries
kalbsfilet
kalbsfleisch
kalbsleber
kalbsleberwurst
kalbsrücken
kalbsschnitzel
kamut
kandiszucker
kaninchen
kapern glas
karambole
karamel
kardamom gemahlen
karottensaft
karpfen
kartoffelbrot
kartoffelchips
kartoffelfasern
kartoffelgratin
kartoffeln gekocht
kartoffeln roh
kartoffelpuffer
kartoffelpüree
kartoffelrösti
kartoffelsalat
kartoffelspalten
kartoffelstärke
kartoffelsuppe
kasseler
katenschinken
kaugummi
kaviar
kefir
kekse
kerbel
ketchup
kichererbsen
kichererbsenmehl
kidneybohnen
kiwi
klöße gekocht
knoblauch
knollensellerie
knuspermüsli
kochschinken
kohlrabi
kohlrübe
kokosblütenzucker
kokosfett
kokosmehl
kokosmilch
kokosnuss
kokosraspel
kokoswasser
kokosöl
konfitüre
konjacnudeln gekocht
konjakmehl
kopfsalat
koriander frisch
koriander gemahlen
kornspitz
krabben
krabbenchips
krakauer wurst
krautsalat
kresse
kreuzkümmel
kroketten
kräuterbutter
kräuterlikör (30%)
kräuterquark 40%
kräutertee
kumquat
kurkuma
kuvertüre vollmilch
kuvertüre zartbitter
käsekuchen
käsespätzle
köfte türkisch
kölsch
körnerbrötchen
kümmel
kümmerling kräuterlikör
kürbis hokkaido
kürbiskernbrötchen
kürbiskerne
kürbiskernmehl
kürbiskernöl
lachs (zucht)
lachsforelle
lachsschinken
lactit
lahmacun
lakritze
lammfilet
lammfleisch
lammkeule
lammkotelett
lammrücken
landjäger
langkornreis roh
languste
lasagne
latte macchiato
laugen
laugenstange
leberknödel
leberkäse
leberwurst
lebkuchen
leinsamen
leinsamenbrot
leinsamenmehl teilentölt
leinöl
liebstöckel
likör
limburger käse
limette
limonade
limonade light
linsen roh
linsensuppe
linzertorte
litschi
lollo rosso
lorbeer getrocknet
lyoner
löffelbisquit
löwenzahn
maasdamer käse
macadamia
mairübchen
mais
maiskolben ganz
maismehl
maisstärke
majoran gerebelt
makrele
makronen
maltit
maltodextrin
malzbier
mamorkuchen
mandarine
mandelmehl
mandelmilch gesüßt
mandelmilch ungesüßt
mandelmus
mandeln
mango
mangold
maniok
margarine
marmelade
marmorkuchen
maronen
marzipan
mascarpone
matjes
maulbeeren
maultaschen
mayonnaise
meerrettich
mehrkornbrot
mehrkornbrötchen
mettenden
mie nudeln
miesmuscheln
milch 03%
milch 15%
milch 35%
milchbrötchen
milchkaffee
milchpulver
milchreis gekocht
milchschnitte
milchzucker
mineralwasser
mischbrot
mischgemüse
mohn
mohnbrötchen
molke
morcheln
moringa pulver
mortadella
mozzarella käse
multivitaminsaft
mungobohnen
muskat gemahlen
möhren / karotten
mürbeteig
müsli
müsliriegel
mwst
nachos mit käse
nackensteak schwein
natron
nektarine
nelken getrocknet
nougat
nudeln gekocht
nudeln eierteigware roh
nudeln hartweizengrieß roh
nudeln vollkorn roh
nudelsalat mit mayonnaise
nuss nougat creme
nussschokolade
nutella
obst getrocknet
obstbrand/obstler (45%)
obstsalat
ofenkäse
oliven grün
oliven schwarz
olivenöl
omelett
orange
orangensaft
oregano getrocknet
ouzo
pak choi
pangasius
paniermehl
panko
papaya
paprika gelb
paprika grün
paprika rot
paprikapulver edelsüß
paprikapulver rosenscharf
paranüsse
parmaschinken fettarm
parmesan käse
passionsfrucht
pastinaken
pecorino
pekanüsse
peperoni
petersilienwurzel
pfeffer
pfefferbeißer
pfefferminze
pfefferminztee
pferdefleisch
pfifferlinge
pfirsich
pflanzenöl
pflaumen
pflaumen getrocknet
pflaumenmus
physalis
pilsner bier
pinienkerne
pistazien
pita
pizzabagett
pizza
pizzateig
polenta
pomelo
pommes
popcorn
porree / lauch
porridge
preiselbeeren
prosecco
puddingpulver vanille
puderzucker
pumpernickel
putenbrust
putenfleisch
putenhackfleisch
putenleber
putensalami
putenschnitzel
putensteak
quark 20%
quark 40%
quark magerquark 05%
quinoa
quitte
radicchio
radieschen
radler
ramazotti kräuterlikör
rapsöl
ratatouille
red bull
red bull sugarfree
rehkeule
rehrücken
reis gekocht parboiled
reis roh parboiled
reismehl
reismilch ungesüßt
reisnudeln roh
reiswaffel
remoulade
rettich
rhabarber
ricotta
rinderbraten
rinderbrühe
rinderfilet
rindergulasch
rinderhackfleisch
rinderhüfte
rinderleber
rinderroulade
rindersteak
rindertalg
rindertatar
rinderzunge
rindswurst
rippchen
risotto
roastbeef
roggenbrot
roggenbrötchen
roggenknäckebrot
roggenmehl
roggenmischbrot
roggenvollkornbrot
roggenvollkornmehl
rohrzucker
rollmops
romadur käse
romanasalat
romanesco
roquefort käse
rosenkohl
rosinen
rosinenbrötchen
rosmarin
roséwein
rotbarsch
rote bete
rote johannisbeeren
rote linsen roh
rote zwiebeln
rotwein
rucola
rum
rumkugel
rumpsteak
rückgeld
räucherlachs
räuchertofu
röstzwiebeln
rübensaft
rührei
rührkuchenteig
sahne 10%
sahne 20%
sahne 30%
sahneies
sahnetorte
saibling
salami
salamipizza
salbei getrocknet
salz
salzstangen
sanddorn
sandkuchen
sardinen
sauce bolognese
sauce hollandaise
sauerkirschen
sauerkirschsaft
sauerkraut
sauerrahm
schafskäse
schalotten
schellfisch
schinken roh geräuchert
schinkenspeck
schinkenwurst
schlagsahne
schmand
schmelzkäse
schnittlauch
schokokuss
schokoladeneis
schokoladenpudding gekocht
schokomüsli
schokoriegel
scholle
schupfnudeln
schwarzbrot
schwarze bohnen
schwarze johannisbeeren
schwarzer johannisbeersaft
schwarzwurzel
schweinebauch
schweinebraten
schweinefilet
schweinegulasch
schweinehackfleisch
schweinehaxe
schweinekotelett
schweineleber
schweinerücken
schweineschmalz
schweineschnitzel paniert und gebraten
schweineschnitzel roh
schweineschulter
schweinezunge
schwertfisch
seehecht
seelachs
seeteufel
seezunge
seitan
sekt halbtrocken
sekt trocken
semmelknödel
senf
serrano schinken
sesam
sesammehl
sesamöl
shiitake pilze frisch
skyr 0.2%
softeis
sojabohne
sojaflocken
sojajoghurt
sojamehl
sojamilch
sojasauce
sojasprossen
sonnenblumenbrot
sonnenblumenkerne
sonnenblumenöl
sorbit
spaghetti bolognese
spaghetti gekocht
spaghetti roh
spare ribs mariniert
spargel
speck
spezi mezzo mix
spiegelei
spinat
spinat mit rahm
spitzkohl
sprite
spätzle
stachelbeeren
stangensellerie
staudensellerie
steckrüben
steinpilze
stevia
stollen
stremellachs
stroh 80
studentenfutter
suppenfleisch rind
suppenhuhn
surimi
sushi
sülze hausmacher art
süßkartoffeln
süßkirschen
süßlupinenmehl
süßstoff
tabasco
tafelspitz mager roh
tahina sesampaste
tamarillo
tamarinde
tee
teewurst
thunfisch
tilsiter käse
tintenfisch
tiramisu
toastbrot
tofu
tomate
tomaten getrocknet
tomaten passiert
tomatenmark
tomatensaft
tomatensauce
tomatensuppe
tonic water
topinambur
tortellini
tortenguss
tortilla
traubenkernöl
traubensaft
traubenzucker
trinkschokolade
trinkwasser
trockenhefe
trüffel
tzatziki
umsatz
vanillepudding gekocht
vanilleschote
vanillezucker
vollkornbrot
vollkornbrötchen
vollkornhaferflocken
vollkornknäckebrot
vollkornreis roh
vollkorntoastbrot
vollmilchschokolade
wacholderbeeren
walnussmehl entölt
walnussöl
walnüsse
wassereis
wassermelone
weinbrand/cognac (40%)
weingeist/sprit (95%)
weinsauerkraut
weinschorle
weintrauben
weizenbier
weizenbier alkoholfrei
weizengras pulver
weizengrieß
weizenkeimöl
weizenkleie
weizenknäckebrot
weizenkorn
weizenmehl 405
weizenstärke
weizenvollkornmehl
weißbier weizenbier
weißbrot
weiße bohnen roh
weiße schokolade
weißkohl
weißwein
weißweinessig
weißweinschorle
weißwurst
welsfilet
weltmeisterbrot
whey protein natur
whisky (40% vol.)
wiener schnitzel gebr.
wiener würstchen
wildente
wildlachs
wildreis gekocht
wildreis gemischt roh
wildreis roh
wildschwein
windbeutel
wirsing
wodka
wurstsalat
xylit
zander
zartbitterschokolade
ziegenfrischkäse
ziegenkäse
zimt
zitrone
zitronengras
zitronensaft
zucchini
zucker
zuckerrübensirup
zuckerschoten
zwieback
zwiebel
zwiebelkuchen
zwiebelmettwurst',
                                        'o_ocr_misc', 'plain-sort', ".$updated.")");
    }

    public function down(){

        $this->execute("DELETE FROM config WHERE group_code='o_ocr_misc'");
    }
}
