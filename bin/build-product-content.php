<?php
/**
 * Builds sql/products-content.sql with real prices, SEO content, and stock
 * for the 89 products imported from the shooting catalogue.
 *
 * Re-run any time you tweak the data array. Then:
 *   mysql -h 127.0.0.1 -u root greenamal < sql/products-content.sql
 */

const STOCK_DEFAULT = 30;
const PRICE_NUM = 0;
const SHORT_NUM = 1;
const LONG_NUM  = 2;
const TAGS_NUM  = 3;

/* slug => [price, short, long, tags] */
$products = [

// ===== Other =====
'greensmile-poudre-dentaire' => [49, 'Poudre dentaire blanchissante au charbon végétal et clous de girofle. Formulée dans le Moyen Atlas.',
    'GreenSmile est notre poudre dentaire 100 % naturelle à base de charbon végétal actif et de clous de girofle moulus. Elle aide à blanchir l\'émail en douceur, à apaiser les gencives et à neutraliser les mauvaises odeurs. Sans fluor, sans parabène, sans glycérine. Un pot suffit pour 2 mois d\'utilisation quotidienne. Trempez votre brosse à dents humide dans la poudre et brossez normalement.',
    'poudre dentaire,charbon végétal,blanchissant,greensmile,naturel'],

// ===== Poudres (4) =====
'reglisse-moulu' => [55, 'Poudre de réglisse 100 % pure (60 g). Anti-pigmentaire, éclat naturel.',
    'La poudre de réglisse pure est traditionnellement utilisée au Maroc pour unifier le teint et atténuer les taches pigmentaires. Riche en glabridine, elle ralentit la production de mélanine et révèle un éclat naturel. Mélangez une cuillère à café à votre masque ou à du yaourt pour le visage. Conservez au sec, à l\'abri de la lumière.',
    'réglisse moulu,anti-tache,glabridine,unifier teint'],

'menthe-pouliot-poudre' => [49, 'Menthe pouliot moulue (70 g) · fraîcheur et purification du cuir chevelu.',
    'La menthe pouliot (Mentha pulegium), surnommée "fliyo" au Maroc, est cueillie à l\'état sauvage dans le Moyen Atlas. Sa poudre s\'incorpore aux masques cheveux et au ghassoul pour purifier le cuir chevelu, apaiser les démangeaisons et apporter une fraîcheur tonifiante. Aussi en infusion digestive, à raison d\'1 c. à café par tasse.',
    'menthe pouliot,fliyo,cheveux,cuir chevelu,infusion'],

'rose-moulue' => [65, 'Pétales de rose de Damas moulus (50 g). Soin visage anti-âge.',
    'Issue des roseraies de Kelaat M\'Gouna, notre poudre de rose conserve l\'huile essentielle naturellement présente dans le pétale. Elle adoucit, hydrate et raffermit la peau. Idéale en masque hebdomadaire mélangée à du yaourt, du miel ou de l\'argile blanche. Son parfum subtil dure des semaines après l\'application.',
    'rose moulue,Damas,Kelaat,anti-âge,masque visage'],

'origan-poudre' => [45, 'Origan moulu (70 g) · antiseptique naturel et exhausteur de saveur.',
    'Cueilli dans les hauteurs du Moyen Atlas et séché à l\'ombre, notre origan conserve toute sa puissance aromatique. Riche en carvacrol, il est traditionnellement utilisé contre les infections respiratoires et pour relever les marinades, pizzas, et viandes grillées. Son arôme intense en fait un incontournable de la cuisine méditerranéenne.',
    'origan,carvacrol,cuisine,antiseptique'],

// ===== Couscous (11) =====
'berkoukch' => [55, 'Berkoukch (couscous gros grain) 1 kg · la pâte rustique de l\'Atlas.',
    'Le berkoukch, ou "ml\'igh", est un couscous à gros grains, roulé à la main par les femmes de la coopérative et séché au soleil. Plus rustique que la semoule fine, il se prête aux plats réconfortants en sauce, aux soupes et au "berkoukch aux légumes" traditionnel. Cuisson 15 min à la vapeur ou à l\'eau frémissante.',
    'berkoukch,couscous gros grain,artisanal,Atlas,rustique'],

'couscous-graines-lin' => [49, 'Couscous aux graines de lin 1 kg · riche en oméga 3.',
    'Notre couscous aux graines de lin allie la tradition berbère à un apport nutritif moderne. Les graines de lin moulues s\'incorporent à la semoule lors du roulage à la main. Source naturelle d\'oméga 3, de fibres et de lignanes, il convient aux régimes santé sans renoncer au plaisir d\'un couscous du vendredi.',
    'couscous,graines de lin,oméga 3,santé,fibres'],

'couscous-mais' => [45, 'Couscous de maïs 1 kg · sans gluten, doux et coloré.',
    'Roulé à la main avec de la semoule de maïs cultivée dans la région d\'Ifrane, ce couscous offre une couleur dorée et une saveur douce, légèrement sucrée. Naturellement sans gluten, il accompagne agneau, légumes mijotés ou simplement un filet d\'huile d\'argan et de raisins secs.',
    'couscous maïs,sans gluten,doré,Ifrane'],

'couscous-sauge' => [49, 'Couscous parfumé à la sauge 1 kg · original et digestif.',
    'Une création originale de la coopérative : la sauge fraîche du Moyen Atlas est intégrée à la semoule pendant le roulage. Le résultat est un couscous au parfum boisé et légèrement camphré, traditionnellement servi avec du poisson, du poulet aux herbes ou en accompagnement de tajines aux légumes verts.',
    'couscous sauge,parfumé,herbes,digestif'],

'couscous-orge' => [45, 'Couscous d\'orge 1 kg · la version berbère ancestrale.',
    'Le couscous d\'orge est la forme la plus ancienne et la plus traditionnelle au Maroc. Riche en fibres et à index glycémique bas, il a un goût rustique et robuste qui se marie particulièrement bien avec les sept légumes et les plats à base de viande de mouton. Roulé manuellement à partir d\'orge cultivée localement.',
    'couscous orge,berbère,fibres,IG bas,traditionnel'],

'couscous-lentilles' => [49, 'Couscous aux lentilles 1 kg · protéiné et complet.',
    'Une innovation nutritive : la farine de lentilles moulues finement est incorporée à la semoule pour un couscous complet en protéines végétales. Idéal pour un plat unique équilibré, accompagné simplement de légumes vapeur et d\'un filet d\'huile d\'olive.',
    'couscous lentilles,protéiné,complet,végétarien'],

'couscous-rouge' => [49, 'Couscous rouge 1 kg · couleur vive, goût intense.',
    'Notre couscous rouge tire sa couleur d\'un mélange de paprika doux et de poudre de tomate séchée incorporés au roulage. Saveur prononcée, idéal en accompagnement de poulet rôti, brochettes ou plats végétariens. Cuisson rapide à la vapeur.',
    'couscous rouge,paprika,tomate,coloré'],

'couscous-complet' => [49, 'Couscous complet 1 kg · toute la fibre du grain entier.',
    'Préparé avec de la semoule complète issue de blé dur non raffiné, ce couscous conserve le son et le germe du grain. Riche en fibres, magnésium et vitamines B, il a un goût plus rustique et un index glycémique plus bas que le couscous blanc classique.',
    'couscous complet,blé entier,fibres,IG bas'],

'couscous-khoumasi' => [55, 'Couscous khoumasi 1 kg · mélange aux 5 céréales.',
    'Le "khoumasi" tire son nom du chiffre 5 en arabe : il combine cinq céréales (blé, orge, maïs, millet et lentilles) pour un profil nutritionnel complet. Une création de la coopérative qui offre une texture variée et un goût terroir profond, parfait pour qui veut redécouvrir le couscous différemment.',
    'couscous,khoumasi,5 céréales,nutrition,complet'],

'couscous-ble' => [45, 'Couscous de blé dur 1 kg · la semoule classique du vendredi.',
    'La référence : couscous de blé dur roulé à la main, séché au soleil, prêt à cuire à la vapeur. Texture légère, grains réguliers, saveur neutre qui se marie avec tous les tajines, viandes et légumes. La semoule du couscous du vendredi traditionnel marocain.',
    'couscous,blé dur,semoule,traditionnel'],

'couscous-herbes' => [49, 'Couscous aux herbes 1 kg · persil, menthe, coriandre.',
    'Mélange d\'herbes fraîches du Moyen Atlas (persil, coriandre, menthe pouliot) intégré au roulage. Couleur verte, parfum frais et digestif, idéal en plat froid type taboulé revisité ou tiède en accompagnement de poisson grillé.',
    'couscous,herbes,persil,coriandre,menthe'],

// ===== Farine (9) =====
'farine-orge-torrefiee' => [39, 'Farine d\'orge torréfiée 500 g · base traditionnelle de la zmita.',
    'L\'orge est légèrement torréfiée puis moulue à la pierre pour obtenir une farine au goût grillé caractéristique. Base de la "zmita" marocaine traditionnelle (mélange avec amande, miel, anis), elle se prépare aussi en bouillie réconfortante pour le petit-déjeuner ou en accompagnement de thé à la menthe.',
    'farine orge torréfiée,zmita,traditionnel,petit-déjeuner'],

'talbina-nabawiya' => [55, 'Talbina nabawiya 500 g · tradition prophétique apaisante.',
    'La talbina est une bouillie d\'orge mentionnée dans la tradition prophétique pour ses vertus apaisantes. Notre version respecte la recette traditionnelle : farine d\'orge fine, à mélanger à de l\'eau ou du lait et à laisser cuire à feu doux. Réconfortante, digestive, elle est traditionnellement consommée en cas de stress, fatigue ou deuil.',
    'talbina,orge,sunna,prophétique,bouillie,apaisant'],

'farine-mais' => [35, 'Farine de maïs 500 g · sans gluten, dorée.',
    'Maïs cultivé dans la région d\'Ifrane, séché et moulu à la pierre. Sans gluten, parfaite pour les pains, galettes type "harcha" et accompagnements traditionnels. Couleur jaune dorée, goût doux et légèrement sucré.',
    'farine maïs,sans gluten,harcha,doré'],

'farine-lentilles' => [42, 'Farine de lentilles 500 g · riche en protéines.',
    'Farine de lentilles moulues fraîchement, riche en protéines végétales (24 %) et en fer. Sans gluten, elle s\'utilise pour épaissir des soupes (harira), faire des galettes salées ou enrichir les pâtes à pain et à pâtisserie. Excellent substitut pour les régimes sans gluten ou enrichis en protéines.',
    'farine lentilles,protéines,sans gluten,fer,harira'],

'farine-ble-torrefiee' => [39, 'Farine de blé torréfiée 500 g · saveur grillée caractéristique.',
    'Blé dur légèrement torréfié avant mouture, ce qui développe une saveur grillée typique. Utilisée traditionnellement pour la "zmita" et certains pains rustiques, elle apporte une note caramélisée distinctive. À mélanger à du miel et de l\'huile d\'argan pour une collation énergétique authentique.',
    'farine blé torréfiée,zmita,grillé,traditionnel'],

'farine-mais-torrefiee' => [39, 'Farine de maïs torréfiée 500 g · couleur ambrée, sans gluten.',
    'Maïs torréfié puis moulu, plus aromatique que la farine de maïs classique. Sans gluten, idéale pour les bouillies, pains de maïs et galettes de fête. Apporte couleur ambrée et goût grillé distinctif.',
    'farine maïs torréfiée,sans gluten,torréfié'],

'farine-pois-chiches' => [45, 'Farine de pois chiches 500 g · protéinée, sans gluten.',
    'Pois chiches du Maroc moulus finement. Très riche en protéines (22 %) et en fibres, elle permet de réaliser falafels, panisses, galettes et même la "panelle" méditerranéenne. Sans gluten, c\'est aussi un excellent liant végétal pour remplacer les œufs en pâtisserie.',
    'farine pois chiches,protéines,sans gluten,falafel'],

'farine-millet' => [45, 'Farine de millet 500 g · céréale ancienne sans gluten.',
    'Millet, céréale ancienne et rustique, moulu finement. Sans gluten, riche en magnésium et en silicium, il s\'utilise pour bouillies, pains plats et pâtisseries. Goût doux et neutre, particulièrement adapté aux régimes spécifiques.',
    'farine millet,sans gluten,céréale ancienne,magnésium'],

'farine-ble-complet' => [39, 'Farine de blé complet 500 g · toute la fibre du grain.',
    'Blé dur complet, moulu avec son et germe, pour une farine riche en fibres, vitamines B et minéraux. Parfaite pour pains rustiques, msemen complet, crêpes et pâtisseries plus nutritives. Goût plus prononcé que la farine raffinée, couleur dorée brun clair.',
    'farine blé complet,fibres,B vitamines,pain rustique'],

// ===== Eau Floral (7) =====
'eau-floral-bleuet' => [69, 'Eau florale de bleuet 200 ml · apaise les yeux et les peaux sensibles.',
    'Distillée à partir de bleuets cultivés au Maroc, notre eau florale apaise les yeux fatigués (en compresse), décongestionne les paupières et calme les peaux sensibles ou irritées. Spray fin pour application directe ou sur coton. Sans alcool, sans conservateur. Conservation 12 mois après ouverture, au frais.',
    'eau florale bleuet,yeux,peau sensible,décongestionnant,hydrolat'],

'eau-floral-fleur-oranger' => [65, 'Eau florale de fleur d\'oranger 200 ml · calmante et parfumée.',
    'Distillée artisanalement à partir de fleurs d\'oranger récoltées au printemps. Calmante pour la peau et les nerfs, elle s\'utilise en pâtisserie marocaine (kaab el ghazal, msemen, thé), en spray rafraîchissant ou ajoutée au bain. Parfum subtil, légèrement sucré.',
    'eau florale,fleur oranger,calmant,pâtisserie,hydrolat'],

'eau-floral-rose' => [69, 'Eau florale de rose 200 ml · tonifiante et anti-âge.',
    'Pétales de rose de Damas distillés à la vapeur d\'eau dans la pure tradition de Kelaat M\'Gouna. Tonifie la peau, resserre les pores, ravive le teint et adoucit les rides. Aussi excellente en cuisine (pâtisseries, salades de fruits) et en lit de bouche pour rafraîchir la respiration.',
    'eau florale rose,Damas,Kelaat,anti-âge,tonique'],

'eau-floral-camomille' => [65, 'Eau florale de camomille 200 ml · apaisante pour peaux réactives.',
    'Camomille romaine distillée à basse température pour préserver les principes actifs. Apaise rougeurs, démangeaisons, coups de soleil et peaux atopiques. Convient aux bébés (sauf application contre-indiquée). Spray pratique, sans alcool, sans conservateur synthétique.',
    'eau florale camomille,apaisant,peau réactive,bébé,hydrolat'],

'greenboost' => [89, 'GreenBoost 100 ml · distillat capillaire fortifiant aux herbes du Moyen Atlas.',
    'GreenBoost est un distillat unique d\'herbes du Moyen Atlas (romarin, jujubier, lavande, menthe, bleuet, rose, camomille, origan, pouliot) destiné à fortifier le cheveu et stimuler la pousse. Vaporisez sur le cuir chevelu propre, massez doucement, ne rincez pas. Utilisation quotidienne. Sans silicones, sans sulfates.',
    'greenboost,distillat,cheveu,fortifiant,pousse,herbes'],

'greenessence' => [89, 'GreenEssence 30 ml · huile parfumée florale pour cheveux et corps.',
    'Une eau de soin parfumée légère qui hydrate, parfume subtilement et soigne sans graisser. Idéale après la douche, en spray dans les cheveux ou sur le corps. Composée d\'hydrolats et d\'huiles essentielles dosées avec précision.',
    'greenessence,huile parfumée,cheveux,corps,subtil'],

'greensilk' => [99, 'GreenSilk 100 ml · huile post-épilation apaisante.',
    'Mélange d\'huiles précieuses (habat sawda, coco, argan, amande douce) et d\'huile essentielle de lavande, GreenSilk apaise immédiatement la peau après épilation, réduit les rougeurs et prévient les poils incarnés. À appliquer en massage doux sur peau encore légèrement humide.',
    'greensilk,post-épilation,apaisant,argan,lavande'],

// ===== Savon (9) =====
'green-ritual-tabrima' => [79, 'Green Ritual Tabrima 100 g · gommage exfoliant aux plantes du Moyen Atlas.',
    'Le tabrima est un rituel berbère de purification du corps. Notre version associe argile rouge, plantes broyées et huile d\'argan pour exfolier en douceur tout en nourrissant. À utiliser en gommage hebdomadaire, sur peau humide, en mouvements circulaires. Rincer abondamment.',
    'tabrima,gommage,argile,plantes,argan,rituel'],

'poudre-jujubier' => [55, 'Poudre de jujubier (sidr) 80 g · soin lavant des cheveux.',
    'Le sidr (jujubier) est utilisé depuis des siècles comme shampooing naturel. Sa poudre lave en douceur sans agresser le cuir chevelu, gaine la fibre capillaire et apporte brillance. Mélangez à de l\'eau tiède jusqu\'à obtenir une pâte, appliquez sur cheveux mouillés, laissez poser 5 minutes, rincez.',
    'jujubier,sidr,poudre lavante,cheveux,naturel'],

'koumaj-jisem' => [69, 'Koumaj el jisem 175 g · gommage corps coloré au gravier de Fès.',
    'Le "koumaj" est un mélange d\'argile et d\'huiles essentielles qui exfolie tout en raffermissant la peau. Notre version au gravier rouge de Fès (akkar fassi) et essences végétales apporte un soin spectaculaire en hammam. Utiliser sur peau mouillée, masser, laisser poser 2 minutes, rincer.',
    'koumaj,gommage corps,akkar fassi,hammam'],

'gommage-cafe' => [69, 'Gommage au café 175 g · anti-cellulite et tonifiant.',
    'Marc de café broyé fin associé à du sucre roux et à de l\'huile de coco. Exfoliant tonifiant, il active la microcirculation, lisse l\'aspect "peau d\'orange" et laisse la peau douce et parfumée. À utiliser 1 à 2 fois par semaine sous la douche.',
    'gommage,café,anti-cellulite,exfoliant,tonifiant'],

'gommage-nila-huiles' => [69, 'Gommage au nila & huiles 175 g · peau lisse et lumineuse.',
    'Le "nila" (indigo) est un trésor de la médecine traditionnelle marocaine : il purifie, apaise et illumine les peaux ternes. Notre gommage associe nila pur, sucre fin et un cocktail d\'huiles précieuses (argan, sésame, jojoba). Idéal en cure pré-événement.',
    'nila,indigo,gommage,huiles,illuminant'],

'greenritual-savon-bleu' => [49, 'GreenRitual Savon bleu 200 g · savon noir au nila.',
    'Notre savon noir Beldi traditionnel à l\'huile d\'olive, enrichi en nila. La couleur bleu-marine signe la richesse en pigment indigo, anti-inflammatoire et illuminant. À utiliser en hammam : appliquer, laisser poser 5 minutes, gommer au gant kessa, rincer.',
    'savon noir,Beldi,nila,hammam,gommage'],

'greenritual-savon-rose' => [49, 'GreenRitual Savon rose 200 g · savon noir aux pétales de rose.',
    'Savon noir Beldi à base d\'huile d\'olive, infusé aux pétales de rose de Damas. Nettoie en profondeur tout en parfumant délicatement la peau. Texture onctueuse, fond douce et durable. Hammam ou douche quotidienne.',
    'savon noir,Beldi,rose,Damas,hammam'],

'greenritual-savon-herbal' => [49, 'GreenRitual Savon herbal 200 g · savon noir aux 7 plantes.',
    'Notre savon noir Beldi enrichi d\'un mélange de 7 plantes du Moyen Atlas (eucalyptus, romarin, lavande, menthe, thym, sauge, origan). Une expérience hammam complète : nettoie, parfume, vivifie. Texture épaisse traditionnelle.',
    'savon noir,Beldi,7 plantes,hammam,herbal'],

'greenmoukhammaria' => [85, 'GreenMoukhammaria 50 g · baume traditionnel multi-usage.',
    'Le "moukhammaria" est un baume berbère traditionnel à base de cire d\'abeille, beurre de karité et huiles essentielles, utilisé sur les lèvres, les coudes, les talons secs ou en application sur les piqûres d\'insectes. Notre version en pot rose conserve la formule originale, fondante et parfumée.',
    'moukhammaria,baume,karité,cire abeille,multi-usage'],

// ===== Huiles essentielles (8) · petits flacons compte-gouttes =====
'he-lavande' => [89, 'Huile essentielle de lavande vraie 15 ml · l\'essentielle apaisante.',
    'Distillée à la vapeur d\'eau à partir de lavande cultivée dans le Moyen Atlas. L\'huile essentielle la plus polyvalente : calme le stress, favorise le sommeil, apaise les piqûres, désinfecte les petites plaies. À diffuser, à diluer dans une huile végétale pour massage, ou en olfaction directe.',
    'huile essentielle,lavande,sommeil,calmant,polyvalent'],

'he-gingembre' => [95, 'Huile essentielle de gingembre 15 ml · tonique et chauffant.',
    'Issue de la racine de gingembre frais distillée. Tonifiante, anti-fatigue, elle réchauffe les muscles avant l\'effort et soulage les nausées en olfaction. Antalgique en massage diluée à 5 % dans une huile végétale.',
    'huile essentielle,gingembre,tonique,nausée,muscle'],

'he-menthe-pouliot' => [85, 'Huile essentielle de menthe pouliot 15 ml · fraîcheur et concentration.',
    'Menthe pouliot sauvage du Moyen Atlas distillée à basse pression. Stimule la concentration, dégage les voies respiratoires et apaise les maux de tête en application diluée sur les tempes. Usage modéré recommandé. Déconseillée femmes enceintes et enfants.',
    'huile essentielle,menthe pouliot,respiratoire,concentration'],

'he-romarin' => [85, 'Huile essentielle de romarin 1.8 cinéole 15 ml · circulation & cheveux.',
    'Romarin de l\'Atlas marocain à fort taux de 1.8 cinéole. Stimule la circulation sanguine, dynamise la pousse capillaire (en massage du cuir chevelu dilué dans une huile végétale) et clarifie l\'esprit. À diffuser le matin pour bien démarrer la journée.',
    'huile essentielle,romarin,cheveu,circulation,1.8 cinéole'],

'he-menthe-poivre' => [89, 'Huile essentielle de menthe poivrée 15 ml · antalgique digestive.',
    'Menthe poivrée distillée. Effet glaçon immédiat sur les coups, antalgique sur les maux de tête, digestive en olfaction ou ingestion (1 goutte sur sucre/miel). Très puissante : usage très modéré, déconseillée femmes enceintes/enfants <12 ans.',
    'huile essentielle,menthe poivrée,digestif,antalgique'],

'he-eucalyptus' => [85, 'Huile essentielle d\'eucalyptus globulus 15 ml · voies respiratoires.',
    'Eucalyptus distillé en respect de la chémotype globulus. Décongestionnant respiratoire majeur en hiver, à diffuser, en inhalation, ou diluée en massage du thorax. Antibactérienne, expectorante. Dilution à 5 % minimum pour la peau.',
    'huile essentielle,eucalyptus,globulus,respiratoire'],

'he-fleur-oranger' => [129, 'Huile essentielle de fleur d\'oranger (néroli) 15 ml · précieuse et apaisante.',
    'Le néroli est une huile précieuse · il faut près d\'une tonne de fleurs pour produire 1 kg d\'essence. Profondément calmante, anti-stress, anti-âge en cosmétique. À diffuser en cas d\'anxiété ou diluée à 1 % dans un sérum visage.',
    'huile essentielle,néroli,fleur oranger,anti-stress,précieuse'],

'he-citron' => [69, 'Huile essentielle de citron zeste 15 ml · purifiante et énergisante.',
    'Zestes de citron pressés à froid. Tonifie l\'organisme, purifie l\'air ambiant en diffusion, soutient la digestion (1 goutte dans une cuillère de miel). Photosensibilisante : éviter l\'application avant exposition au soleil.',
    'huile essentielle,citron,zeste,énergisant,purifiant'],

// ===== PAM (17) =====
'pam-alward-rose' => [55, 'Pétales de rose séchés (alward) 50 g · décoration & infusion.',
    'Pétales de rose de Damas du Moyen Atlas, séchés à l\'ombre pour préserver couleur et arôme. À infuser pour une boisson tonique, à parsemer dans une salade ou un dessert, ou simplement à exposer dans un bol parfumé. Récolte de printemps.',
    'rose séchée,alward,Damas,infusion,décoration'],

'pam-girofle' => [49, 'Clous de girofle 50 g · épice et antiseptique buccal.',
    'Boutons floraux du giroflier, séchés. Indispensable des tajines, du ras el hanout et du thé d\'hiver. Antiseptique buccal naturel : un clou mâché soulage les rages de dent. Conservation longue durée à l\'abri de l\'air.',
    'girofle,épice,antiseptique,tajine'],

'pam-bleuet' => [55, 'Bleuet séché 20 g · tisane apaisante et déco florale.',
    'Fleurs de bleuet (centaurea cyanus) cueillies puis séchées délicatement pour conserver leur bleu profond. À infuser pour une tisane apaisante des yeux et de la peau, ou à parsemer dans un mélange déco-pâtisserie.',
    'bleuet,fleur séchée,tisane,décoration'],

'pam-greencalme' => [69, 'GreenCalme · mélange apaisant 70 g (camomille, lavande, mélisse, menthe, marjolaine).',
    'Notre mélange signature apaisant pour la fin de journée : camomille, lavande, mélisse, menthe pouliot, marjolaine et romarin. À infuser 5 minutes dans de l\'eau chaude (1 c. à soupe par tasse). Aide à dénouer le stress, favoriser le sommeil et la digestion. Composition 100 % plantes du Moyen Atlas.',
    'greencalme,tisane apaisante,sommeil,stress,mélange'],

'pam-romarin' => [35, 'Romarin séché 50 g · cuisine et tisane tonique.',
    'Romarin officinal cueilli à la main puis séché à l\'ombre. Aromate méditerranéen incontournable : viandes grillées, légumes rôtis, tajines. En infusion, il tonifie la mémoire et stimule la digestion.',
    'romarin séché,herbe,tisane,cuisine'],

'pam-jujubier' => [45, 'Feuilles de jujubier 70 g · soin cheveux traditionnel.',
    'Feuilles de jujubier (sidr) cueillies puis séchées entières. À broyer fraîchement pour usage cosmétique cheveux, ou à infuser pour une tisane purifiante.',
    'jujubier,sidr,feuille,cheveux,traditionnel'],

'pam-lavande' => [45, 'Lavande séchée 70 g · sommeil & parfum d\'armoire.',
    'Sommités fleuries de lavande coupées en bouquets et séchées. À glisser dans les armoires pour parfumer le linge, à infuser le soir pour apaiser, ou à utiliser en sachet sous l\'oreiller pour favoriser le sommeil.',
    'lavande séchée,sommeil,parfum armoire'],

'pam-menthe-pouliot' => [29, 'Menthe pouliot séchée (fliyo) 70 g · digestive du Moyen Atlas.',
    'Menthe pouliot sauvage cueillie dans les hauteurs de l\'Atlas. Très digestive en infusion après les repas. Note : déconseillée chez la femme enceinte. À utiliser modérément en cuisine.',
    'menthe pouliot,fliyo,tisane digestive'],

'pam-camomille' => [39, 'Camomille séchée 70 g · apaisante & sommeil.',
    'Capitules floraux de camomille romaine séchés délicatement. La tisane d\'avant-coucher par excellence : favorise le sommeil, apaise les troubles digestifs, calme l\'anxiété. Aussi en compresse sur les yeux fatigués.',
    'camomille séchée,sommeil,digestif,apaisant'],

'pam-marjolaine' => [35, 'Marjolaine séchée 70 g · herbe aromatique digestive.',
    'Marjolaine (proche cousine de l\'origan) cueillie puis séchée. Aromate doux et digestif, parfait dans les sauces tomate, les marinades et les viandes blanches. En tisane, elle calme les spasmes intestinaux.',
    'marjolaine,herbe aromatique,digestif'],

'pam-sauge' => [35, 'Sauge officinale séchée 70 g · antiseptique des sphères ORL.',
    'Sauge officinale cueillie à la floraison, séchée à l\'ombre. En gargarisme (infusion concentrée), elle soulage les maux de gorge. En cuisine, elle parfume les viandes blanches, le couscous, et les beurres.',
    'sauge,gorge,gargarisme,cuisine'],

'pam-melisse' => [49, 'Mélisse séchée 70 g · calme et digestif.',
    'Mélisse officinale cueillie au matin pour préserver ses huiles essentielles. Tisane apaisante, anti-stress, calme les troubles digestifs liés à l\'anxiété. Goût citronné caractéristique. Bonne complémentation avec la camomille en mélange.',
    'mélisse,citron,calmant,digestif'],

'pam-thym' => [35, 'Thym séché 70 g · antiseptique respiratoire.',
    'Thym sauvage de l\'Atlas, plus parfumé que le thym commun, riche en thymol. En infusion, il combat efficacement rhumes, maux de gorge et toux. En cuisine, base des marinades et bouillons.',
    'thym sauvage,thymol,respiratoire'],

'pam-verveine' => [49, 'Verveine séchée 50 g · l\'infusion d\'après-dîner.',
    'Verveine officinale séchée doucement pour conserver son parfum citronné si reconnaissable. Digestive, légèrement sédative, c\'est l\'infusion d\'après-dîner par excellence. Une cuillère par tasse, infusion 5 minutes.',
    'verveine,citronné,digestif,tisane'],

'pam-eucalyptus' => [39, 'Eucalyptus séché 70 g · voies respiratoires.',
    'Feuilles d\'eucalyptus globulus séchées. À utiliser en inhalation pour décongestionner les voies respiratoires en hiver, ou en tisane (1 c. à café par tasse). Aussi à brûler pour assainir une pièce.',
    'eucalyptus,feuille,respiratoire,inhalation'],

'pam-moringa' => [69, 'Moringa séché 50 g · superfood riche en nutriments.',
    'Feuilles de moringa oleifera, surnommé "arbre miraculeux" pour sa densité nutritionnelle exceptionnelle (vitamines A, C, calcium, fer, protéines). En infusion ou en poudre dans smoothies et soupes pour une cure énergisante.',
    'moringa,superfood,vitamines,nutriment'],

'pam-origan' => [35, 'Origan séché 70 g · l\'incontournable méditerranéen.',
    'Origan sauvage du Moyen Atlas séché à l\'ombre. Saveur intense, parfaite sur les pizzas, salades de tomates, marinades de poulet. En tisane, il soulage les voies respiratoires.',
    'origan séché,herbe,méditerranéen'],

// ===== Huiles végétales (14) =====
'huile-massage' => [79, 'Huile de massage 50 ml · détente musculaire et bien-être.',
    'Mélange équilibré d\'huiles végétales (sésame, amande douce, coco) et d\'huiles essentielles relaxantes (lavande, marjolaine). Pénètre rapidement sans laisser de film gras, parfaite pour un massage du dos, des épaules ou des jambes lourdes.',
    'huile massage,détente,lavande,relaxant'],

'huile-graines-lin' => [69, 'Huile de graines de lin 50 ml · riche en oméga 3.',
    'Huile vierge de première pression à froid, à conserver au réfrigérateur après ouverture. Très haute concentration en oméga 3 (acide alpha-linolénique). À consommer crue uniquement, à raison d\'une cuillère à soupe par jour, pour soutenir cœur, cerveau et inflammation.',
    'huile lin,oméga 3,santé,cru'],

'huile-rose' => [89, 'Huile de rose musquée 50 ml · anti-âge cicatrisante.',
    'Huile précieuse extraite des graines de rosier muscat. Riche en acides gras essentiels, vitamine A et antioxydants. Régénère les cellules, atténue les cicatrices et les vergetures, prévient le vieillissement cutané. Quelques gouttes en sérum visage le soir.',
    'huile rose musquée,anti-âge,cicatrice,vergeture'],

'huile-jojoba' => [89, 'Huile de jojoba 50 ml · l\'huile équilibrante.',
    'Cire liquide proche du sébum humain, le jojoba équilibre les peaux mixtes à grasses, hydrate sans graisser et démaquille en douceur. Aussi excellente sur cheveux secs (en bain d\'huile) et pour adoucir le contour de l\'œil.',
    'jojoba,équilibrant,sébum,démaquillant'],

'huile-camomille' => [85, 'Huile de camomille 50 ml · apaisante peaux sensibles.',
    'Macérat huileux de camomille dans une huile de tournesol bio. Apaise rougeurs, irritations, eczéma léger. Convient aux peaux ultra-sensibles et aux bébés. Application fine en massage doux ou en compresse.',
    'huile camomille,apaisant,peau sensible,bébé'],

'huile-argan-fleur-oranger' => [99, 'Huile d\'argan à la fleur d\'oranger 50 ml · soin parfumé.',
    'Notre huile d\'argan pure macérée délicatement avec des fleurs d\'oranger pour un parfum subtil et envoûtant. Tous les bienfaits anti-âge de l\'argan, plus une expérience sensorielle apaisante. Visage, corps, cheveux.',
    'argan,fleur oranger,parfumé,anti-âge'],

'huile-sesame' => [55, 'Huile de sésame 50 ml · vierge première pression à froid.',
    'Huile de sésame vierge extra, première pression à froid. Couleur ambrée, goût de noisette grillée. En cuisine pour parfumer salades et plats asiatiques, ou en cosmétique pour nourrir et protéger la peau (anti-UV léger).',
    'huile sésame,vierge,cuisine,cosmétique'],

'huile-argan' => [129, 'Huile d\'argan 50 ml · l\'or liquide du Maroc.',
    'Première pression à froid, non torréfiée, pressée à la main par les femmes de la coopérative. Riche en vitamine E, oméga 6 et oméga 9. Nourrit la peau, fortifie cheveux et ongles, ralentit le vieillissement cutané. Quelques gouttes suffisent.',
    'argan,or liquide,vitamine E,anti-âge,Maroc'],

'huile-graine-oignon' => [85, 'Huile de graine d\'oignon 50 ml · anti-chute capillaire.',
    'Huile macérée de graines d\'oignon noir, traditionnellement utilisée au Maroc pour stimuler la pousse des cheveux et combattre la chute. Massage du cuir chevelu 2 fois par semaine, laisser poser 30 min minimum, rincer au shampoing doux.',
    'huile oignon,anti-chute,pousse cheveux'],

'huile-amande-amere' => [69, 'Huile d\'amande amère 50 ml · peau et cheveux.',
    'Macérat d\'amandes amères. Adoucit la peau, atténue les vergetures et les cernes. En cuisine (avec parcimonie), parfume gâteaux et confiseries.',
    'amande amère,peau,vergetures'],

'huile-romarin' => [75, 'Huile de romarin 50 ml · pousse capillaire.',
    'Macérat huileux de romarin dans une huile d\'olive vierge. Stimule la circulation du cuir chevelu et favorise la pousse capillaire. Massage en brosses énergiques 1 à 2 fois par semaine.',
    'huile romarin,cuir chevelu,pousse cheveux'],

'huile-anti-chute' => [99, 'Huile anti-chute 100 ml · synergie de 27 plantes & 15 huiles.',
    'Notre formule signature anti-chute combine 27 extraits de plantes médicinales (jujubier, romarin, lavande, henné…) macérés dans 15 huiles végétales (argan, ricin, oignon, sésame, coco…). Massage du cuir chevelu, laisser poser 3 heures, shampoing doux. 1 à 2 fois par semaine.',
    'anti-chute,cheveu,plantes,signature,fortifiant'],

'huile-graine-noire' => [85, 'Huile de graine noire (nigelle) 50 ml · la "guérisseuse" de la sunna.',
    'Habat sawda · la nigelle · pressée à froid, non raffinée. Réputée pour ses vertus immuno-stimulantes (1 c. à café à jeun). Aussi en cosmétique pour les peaux à imperfections et les cheveux fragilisés. Tradition prophétique : "remède pour tout sauf la mort".',
    'nigelle,habat sawda,immunité,sunna'],

'huile-lavande' => [69, 'Huile de lavande 50 ml · apaisante & cicatrisante.',
    'Macérat huileux de fleurs de lavande dans une huile végétale neutre. Apaise les piqûres d\'insectes, favorise la cicatrisation, calme la peau irritée. Parfum subtil, idéale en huile relaxante du soir.',
    'huile lavande,apaisant,cicatrisant'],

// ===== Packs (9) =====
'pack-talbina' => [199, 'Pack Talbina · talbina nabawiya, miel et fruits secs.',
    'Notre coffret cadeau "Sunna" combine la talbina nabawiya (500 g), un pot de miel local et un sachet de fruits secs (figues, dattes, noix). Présenté dans une boîte cadeau soignée avec ruban. Idéal pour offrir à un proche en convalescence ou en signe de soin.',
    'pack,talbina,miel,cadeau,sunna'],

'pack-cosmetique' => [449, 'Pack Cosmétique · la routine GreenAmal complète (7 produits).',
    'Le coffret découverte de notre gamme cosmétique : GreenEssence (huile parfumée), GreenBoost (distillat capillaire), GreenSilk (post-épilation), Green Ritual Tabrima (gommage), GreenRitual Savon, GreenMoukhammaria (baume), et GreenSmile (poudre dentaire). Présenté dans une box élégante.',
    'pack,cosmétique,coffret,routine,découverte'],

'pack-eaux-florales' => [249, 'Pack 4 Eaux florales · Rose, Bleuet, Camomille, Fleur d\'Oranger (4×200 ml).',
    'Notre quatuor d\'eaux florales (hydrolats) : Rose de Damas, Bleuet, Camomille romaine, Fleur d\'Oranger. Pour la peau, les yeux, la cuisine et l\'apaisement. Présenté en coffret cadeau avec sprays prêts à l\'emploi.',
    'pack,eaux florales,hydrolat,coffret,quatuor'],

'pack-rose' => [199, 'Pack Rose · la rose sous toutes ses formes.',
    'Coffret thématique autour de la rose de Damas : eau florale (200 ml), pétales séchés (alward, 50 g), rose moulue (50 g), huile de rose musquée (50 ml). Pour soigner la peau, parfumer la cuisine et embellir le quotidien.',
    'pack,rose,Damas,coffret cadeau'],

'pack-huiles-essentielles' => [349, 'Pack 8 Huiles essentielles · découverte aromathérapie.',
    'Découvrez l\'aromathérapie avec nos 8 huiles essentielles signature : Lavande, Romarin, Eucalyptus, Menthe poivrée, Menthe pouliot, Gingembre, Fleur d\'oranger, Citron. Présentées en coffret bois avec compte-gouttes. Guide d\'utilisation inclus.',
    'pack,huiles essentielles,aromathérapie,coffret'],

'pack-huiles-vegetales' => [499, 'Pack 14 Huiles végétales · l\'expérience GreenAmal.',
    'Notre collection complète d\'huiles végétales : argan, jojoba, rose musquée, sésame, lin, anti-chute, graine noire, et bien plus. Du soin capillaire au visage, de l\'alimentation au massage : un essentiel pour qui aime les soins naturels.',
    'pack,huiles végétales,collection,coffret'],

'pack-poudres' => [129, 'Pack 4 Poudres · Réglisse, Menthe pouliot, Rose, Origan.',
    'Quartet de poudres végétales pour cuisine et cosmétique : réglisse moulu (60 g), menthe pouliot (70 g), rose moulue (50 g), origan moulu (70 g). Pour vos masques, infusions ou plats raffinés.',
    'pack,poudres,quatuor,cosmétique'],

'pack-gommage-cafe-savon' => [129, 'Pack Gommage Café & Savon Beldi · rituel hammam.',
    'Le duo essentiel de la routine hammam : Gommage au Café (175 g, anti-cellulite) et GreenRitual Savon Beldi (200 g). Pour une peau lisse, douce et tonifiée. Mode d\'emploi dans la box.',
    'pack,gommage,savon,hammam,rituel'],

'pack-koumaj-nila' => [149, 'Pack Koumaj & Nila · duo gommage spectaculaire.',
    'Notre duo gommage le plus puissant : Koumaj el Jisem (gravier de Fès) et Gommage au Nila (indigo) en 2×175 g. Pour une exfoliation complète et un éclat immédiat avant un événement.',
    'pack,koumaj,nila,gommage'],

];

/* ----------------------------------------------------------------------- */

$out = "-- Generated by bin/build-product-content.php\n";
$out .= "-- SEO content for the 89 products of the shooting catalogue\n\n";
$out .= "USE greenamal;\n\n";

foreach ($products as $slug => $p) {
    [$price, $short, $long, $tags] = [$p[0], $p[1], $p[2], $p[3]];
    $name = ucfirst(str_replace('-', ' ', $slug));
    $meta_title = mb_strimwidth(strip_tags($short), 0, 65, '');
    $meta_desc  = mb_strimwidth(strip_tags($long),  0, 160, '');

    $out .= sprintf(
        "UPDATE products SET\n  price = %.2f, stock = %d, status = 'active',\n  description_short = '%s',\n  description_long = '%s',\n  meta_title = '%s',\n  meta_description = '%s',\n  tags = '%s'\nWHERE slug = '%s';\n\n",
        $price, STOCK_DEFAULT,
        esc($short), esc($long), esc($meta_title), esc($meta_desc), esc($tags), esc($slug)
    );
}

$out_path = __DIR__ . '/../sql/products-content.sql';
file_put_contents($out_path, $out);
echo "Wrote $out_path · " . count($products) . " products · " . number_format(strlen($out)) . " bytes.\n";
echo "Run:\n  mysql -h 127.0.0.1 -u root greenamal < $out_path\n";

function esc(string $s): string { return str_replace("'", "''", $s); }
