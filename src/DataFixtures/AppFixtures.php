<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

use App\Entity\User;
use App\Entity\MAPmap;
use App\Entity\MAPlocation;

use App\Repository\UserRepository;
use App\Repository\MAPmapRepository;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
      $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // ************************************************************
        // User Gebo
        // ************************************************************

        $usr = new User();
        $usr->setName("Gebo")
            ->setEmail('gebo@none.null')
            ->setRoles(['ROLE_ADMIN'])
            ->setPlainPassword('password')
            ->setLanguage('fr')
        ;
        $manager->persist($usr);

        // Map #1 for Gebo

        $map = new MAPmap();
        $map->setName("Seine et Marne")
            ->setDescription("Lieux à visiter en Seine et Marne.")
            ->setPrivate(false)
            ->setPassword('')
            ->setLatitude(48.58206)
            ->setLongitude(2.99797)
            ->setZoom(9)
            ->setUser($usr)
        ;
        $manager->persist($map);

        // Locations for map #1 for Gebo

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Provins")
            ->setDescription("Cité médiévale, ancienne capitale des comtes de Champagne, inscrite sur la liste du patrimoine mondial de l'UNESCO.")
            ->setLatitude(48.56042)
            ->setLongitude(3.28076)
            ->setLink('https://www.france-voyage.com/francia-guia-turismo/provins-1486.htm')
            ->setIcon('castle.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Vaux-le-Vicomte")
            ->setDescription("Château du XVIIe siècle construit pour le surintendant des finances de Louis XIV, Nicolas Fouquet.")
            ->setLatitude(48.56844)
            ->setLongitude(2.7137)
            ->setLink('https://vaux-le-vicomte.com/en/')
            ->setIcon('castle.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Moret-sur-Loing")
            ->setDescription("Cité médiévale en lisière de la forêt de Fontainebleau traversée par le Loing.")
            ->setLatitude(48.37376)
            ->setLongitude(2.81443)
            ->setLink('https://www.laguiadeparis.com/visita-moret-sur-loing/')
            ->setIcon('city.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Gorges de Franchard")
            ->setDescription("Du chêne Georges Sand à l'Oeil-des-Nations, un circuit à la rencontre des paysages caractéristiques de la forêt de Fontainebleau : paysage de landes et de chaos, de mares mystérieuses et de hautes chênaies.")
            ->setLatitude(48.40874)
            ->setLongitude(2.63808)
            ->setLink('https://www.parissurunfil.com/randonnee-sentier-ermitage-franchard/')
            ->setIcon('forest.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Festival Django Reinhardt")
            ->setDescription("Au mois de juin, la place du village de Samois-sur-Seine se transforme chaque année pour une soirée en fête populaire, conviviale qui marque l'ouverture du Festival Django Reinhardt. Le festival se poursuit ensuite à Fontainebleau.")
            ->setLatitude(48.45156)
            ->setLongitude(2.74987)
            ->setLink('https://www.festivaldjangoreinhardt.com/programmation/samois/')
            ->setIcon('festival.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Château de Fontainebleau")
            ->setDescription("Le château de Fontainebleau est un château impérial de styles principalement Renaissance et classique, au centre-ville de Fontainebleau.")
            ->setLatitude(48.40364)
            ->setLongitude(2.69934)
            ->setLink('https://www.chateaudefontainebleau.fr/')
            ->setIcon('castle.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Commanderie des Templiers")
            ->setDescription("Construite entre le XIIe et le XVe siècle, la commanderie templière de Coulommiers constitue l'ensemble Templier le mieux conservé au nord de la Loire.")
            ->setLatitude(48.82439)
            ->setLongitude(3.09421)
            ->setLink('https://www.coulommiers.fr/loisirs/patrimoine/la-commanderie-des-templiers/')
            ->setIcon('templarcross.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Château de Blandy les Tours")
            ->setDescription("Le château de Blandy-les-Tours est un château fort construit entre le XIIIe et la seconde moitié du XIVe siècle.")
            ->setLatitude(48.56711)
            ->setLongitude(2.78226)
            ->setLink('https://fr.wikipedia.org/wiki/Ch%C3%A2teau_de_Blandy-les-Tours')
            ->setIcon('castle.png')
        ;
        $manager->persist($loc);

        // Map #2 for Gebo

        $map = new MAPmap();
        $map->setName("Montserrat")
            ->setDescription("Visite du monastère de la montagne de Montserrat.")
            ->setPrivate(false)
            ->setPassword('pass')
            ->setLatitude(41.59388)
            ->setLongitude(1.83961)
            ->setZoom(14)
            ->setUser($usr)
        ;
        $manager->persist($map);

        // Locations for map #2 for Gebo

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Monastère")
            ->setDescription("L'abbaye Santa Maria de Montserrat est une abbaye bénédictine autonome située sur le massif montagneux de Montserrat.")
            ->setLatitude(41.59393)
            ->setLongitude(1.83958)
            ->setLink('https://www.montserrat-tourist-guide.com/fr/attractions/monastere-montserrat.html')
            ->setIcon('temple.png')
        ;
        $manager->persist($loc);

        // ************************************************************
        // User Ana
        // ************************************************************

        $usr = new User();
        $usr->setName("Ana")
            ->setEmail('ana@none.null')
            ->setRoles(['ROLE_USER'])
            ->setPlainPassword('password')
            ->setLanguage('ca')
        ;
        $manager->persist($usr);

        // Map #1 for Ana

        $map = new MAPmap();
        $map->setName("Sant Cugat")
            ->setDescription("Visita de Sant Cugat")
            ->setPrivate(false)
            ->setPassword('')
            ->setLatitude(41.47016)
            ->setLongitude(2.08896)
            ->setZoom(15)
            ->setUser($usr)
        ;
        $manager->persist($map);

        // Locations for map #1 for Ana

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Monasterio")
            ->setDescription("Monasterio benedictino")
            ->setLatitude(41.47341)
            ->setLongitude(2.08438)
            ->setLink('https://visit.santcugat.cat/es/monasterio-de-sant-cugat/')
            ->setIcon('temple.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Celler cooperatiu")
            ->setDescription("Celler modernista")
            ->setLatitude(41.47179)
            ->setLongitude(2.08546)
            ->setLink('https://visitvalles.com/es/portfolio_page/celler-modernista-museu-de-sant-cugat/')
            ->setIcon('monument.png')
        ;
        $manager->persist($loc);

        $loc = new MAPlocation();
        $loc->setMap($map)
            ->setName("Pi d'en Xandri")
            ->setDescription("Árbol histórico")
            ->setLatitude(41.46741)
            ->setLongitude(2.10054)
            ->setLink('https://visitvalles.com/es/portfolio_page/el-pi-den-xandri/')
            ->setIcon('forest.png')
        ;
        $manager->persist($loc);

        // Commit

        $manager->flush();
    }
}
