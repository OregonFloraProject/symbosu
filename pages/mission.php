﻿<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Our Mission</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">  
    <meta name='keywords' content=''/>
    <script type="text/javascript">
		<?php include_once( $serverRoot . '/config/googleanalytics.php' ); ?>
    </script>

</head>
<body>
<?php
      include("$SERVER_ROOT/header.php");
?>
<div class="info-page">
    <section id="titlebackground" class="title-leaf">
        <div class="inner-content">
            <h1>Mission and History</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
        <!-- place static page content here. -->
            <h2 class="subhead">Our mission is to increase awareness and knowledge of the plants of Oregon through publication of technically sound, accessible information for diverse audiences.</h2>
            <p>
            OregonFlora has assembled a comprehensive guide to the ~4,750 vascular plants of Oregon that is shared through this website, the Flora of Oregon books,
             and our wildflower identification app. 
             Our foundational research supports diverse audiences &mdash; citizen scientists, students and gardeners, ecologists and land managers, 
             policy makers, university researchers and leaders &mdash; in the critical work of protecting biodiversity, managing natural resources, promoting sustainability, 
             and furthering understanding of our botanical treasures.
            </p>
            <div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img
                                srcset="images/student_looking_at_plant.jpg 1x"
                                src="images/student_looking_at_plant.jpg"
                                class="figure-img img-fluid z-depth-1"
                                alt="Student looking at plant">
                        <figcaption class="figure-caption">Students contribute significantly to the research and activities of the program.</figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h2>&nbsp;Scope of Project</h2>
                    <p>OregonFlora addresses the ~4,750 vascular plant species, subspecies, and varieties (taxa) of Oregon that grow in the wild without cultivation. These include:</p>
                    <ul>
                        <li>all extant native taxa</li>
                        <li>native taxa thought to have gone extinct in Oregon in historical times</li>
                        <li>exotic (non-native), cultivated, or weedy taxa that have naturalized</li>
                        <li>interspecific hybrids that are frequent or self-maintaining</li>
                        <li>infrequently collected exotic taxa (e.g., ballast plants and current waifs)</li>
                        <li>unnamed taxa in process of being described</li>
                    </ul>
                    <p>We define <strong>“native”</strong> as a plant taxon which has established in the landscape independently from direct or indirect human intervention. Native species include those found in Oregon that are new to science and recently described, are disjunct in Oregon if it is considered native in a nearby state, and/or are—to the best of our knowledge—considered an element of Oregon plant life prior to European settlement.</p>
                    
                    <p>A non-native, or exotic plant, is one from distant parts of North America or from other 
                    continents that established in Oregon post-European settlement. 
                    Examples include weeds, naturalized escapes, waifs, and ballast plants.
                    </p>
                    <p>Two categories of non-native plants fall within the scope of the project:</p>
                    	<ul>
                    		<li>Escaped cultivated plants: agricultural and garden taxa that have persisted in the wild for at 
                    		least 3-5 years and have spread beyond the area where they were originally cultivated.</li>
                    		<li>Noncultivated exotic plants: weeds (nuisance non-native taxa), waifs (solitary or small groups of 
                    		non-native plants persisting for only one season), ballast plants (waifs growing on ship ballast).</li>
                    	</ul>

                    
                    
                </div>
            </div>
            <div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img
                                srcset="images/volunteer4.jpg 1x"
                                src="images/volunteer4.jpg"
                                class="figure-img img-fluid z-depth-1"
                                alt="Photographing a flower">
                        <figcaption class="figure-caption">Photos and species lists contributed by individuals, agencies, and the Native Plant Society of Oregon help co-create the rich plant diversity resources OregonFlora offers to the public. </figcaption>
                    </figure>
                    <figure class="figure">
                        <img src="images/ofp_atlas_map.jpg" class="figure-img img-fluid z-depth-1" alt="OFP map">
                        <figcaption class="figure-caption">A map generated from the Oregon Flora Project’s first mapping program. An interactive version of the program was added to the website in 2005.</figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h2>History</h2>
                    <h3>1994-2004</h3>
                    <p>Our program, then the Oregon Flora Project (OFP), was begun in 1994 by Scott Sundberg at Oregon State University as an effort to prepare a new flora of the vascular plants of Oregon. To ensure that the information remained comprehensive and up-to-date, a database was created to keep track of the nomenclature, synonymy, and literature references for all of Oregon’s plants. It also was used to develop a version of the Flora that could be presented online as an interactive, digital resource.</p>
                    <p>The Oregon Plant Atlas was initiated in 1995, and used the skills of geographers, programmers, and botanists to create an interactive online tool for mapping plant occurrence data from the Oregon Flora Project databases. This effort received a significant boost in 2001 with a grant from the OR/WA Bureau of Land Management to add to the database a record for each taxon found in every county. Sundberg hired the first staff members at this time, including Thea Jaster and later Katie Mitchell.</p>
                    <figure class="figure figure-inline">
                        <img
                            srcset="images/oxa_ore_2338b.png 1x, images/oxa_ore_2338b@2x.png 2x"
                            src="images/oxa_ore_2338b.png"
                            class="figure-img img-fluid z-depth-1"
                            alt="Volunteer 2">
                        <figcaption class="figure-caption">Oxalis oregana, or Oregon wood sorrel (Gerald D. Carr).</figcaption>
                    </figure>
                    <p>Collaboration with the Northwest Alliance for Computational Science and Engineering at OSU resulted in the award of a grant (2001-2004) from the National Science Foundation to design and develop software for presenting Oregon Flora Project data online. A Photo Gallery was also added featuring field photos contributed by plant enthusiasts; the plant identification for each was confirmed by OFP staff or skilled volunteers prior to posting online. Digitized images of herbarium specimens were added as a component of the Photo Gallery.</p>
                    <p>The Oregon State University Herbarium and the Oregon Flora Project collaborated on a National Science Foundation proposal (2003-2006) to database and georeference label data from all Oregon herbarium specimens not yet included in the Oregon Plant Atlas. OregonFlora maintains this dataset and serves as the public face to the OSU Herbarium by providing images of herbarium specimens, searchable label data, and the ability to map plant occurrences through the OregonFlora website.</p>
                </div>
            </div>
            <div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img
                                srcset="images/history_2004_scott_sundberg.jpg 1x"
                                src="images/history_2004_scott_sundberg.jpg"
                                class="figure-img img-fluid z-depth-1"
                                alt="Founding director Scott Sundberg.">
                        <figcaption class="figure-caption">Founding director Scott Sundberg.</figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h3>2004-2010</h3>
                    <p>In December 2004, the Oregon Flora Project suffered a great loss with the death of its director and founder, Scott Sundberg. The program continued, however, following a path to its completion that was Scott’s vision. Linda Hardison assumed the position of director, and existing staff members maintained their essential roles.</p>
                    <p>In early 2008 the Project was halted indefinitely due to a lack of funds. Through the timely and generous support of the John and Betty Soreng Environmental Fund of the Oregon Community Foundation, all staff members were rehired, and the operations of the Project resumed in Autumn 2008. The sustained support of this fund has allowed OFP to bring to fruition public access to every facet of the Project through its “digital flora” website: the Photo Gallery (2009), version 2.0 of the Oregon Plant Atlas (2010), and the Vascular Plant Checklist (2011). It also enabled initiation of the production of the Flora volumes.</p>
                </div>

            </div>
            <div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img
                                srcset="images/volunteer1.jpg 1x"
                                src="images/volunteer1.jpg"
                                class="figure-img img-fluid z-depth-1"
                                alt="Volunteer">
                        <figcaption class="figure-caption">Tabling events at wildflower shows, gardening events, and community fairs share information about OregonFlora with the public.</figcaption>
                    </figure>
                    <figure class="figure">
                        <img src="images/history_ag_field_dam.jpg" class="figure-img img-fluid z-depth-1" alt="Restoring wetland function to parts of an agricultural field">
                        <figcaption class="figure-caption">Restoring wetland function to parts of an agricultural field. By building a sandbag dam to partially block a culvert, water is held on the landscape. The area was later seeded with native species that also help to support grazers. </figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h3>2010-2020</h3>
                    <p>With the Checklist serving as a robust foundation, OFP efforts focused on the production of the printed Flora of Oregon. Stephen Meyers was hired in 2010 as the taxonomic director to oversee the writing of the floristic treatments and identification keys. In 2012 artist John Myers joined the staff to contribute artwork for the first ever illustrated flora for the state of Oregon. That same year, an eleven-member advisory board was established, with members representing the diversity of stakeholders that are informed by the work of the OFP. BRIT Press was selected to publish the flora, and with the design and layout expertise of Tanya Harvey, Volume 1 of the Flora of Oregon was published in September 2015. </p>
										<p>As the body of knowledge for the nearly 4,750 plant taxa grew, so did the capacity to apply it. An OFP strategic plan, first drafted in 2015, identified three strategic initiatives:  using native species in gardens and planted environments, increasing biodiversity in working agricultural lands, and science education. </p>
										<p>Through a partnership with Metro (Portland) and the Adult Conservation Educators Northwest, OFP assumed oversight of their dataset of native plants used for gardening and landscaping in western Oregon. Funding from the Oregon Dept. of Agriculture’s Specialty Crop Block Grant program (2014-2016) helped OFP develop information promoting use of natives in gardening and, with Metro, design an interactive portal to share the data on the OFP’s redesigned website.</p>
										<p>In 2016, the OFP initiated a redesign of its website using the Symbiota software platform. This has allowed adoption of more versatile ways to analyze and communicate information, and the linking of OFP plant data to other datasets. The program also changed its name to OregonFlora. </p>
										<p>As part of Oregon State University, the state’s land grant institution, OregonFlora is researching effective ways to return plant diversity and natural habitats to working agricultural lands. Grants from the Oregon Watershed Enhancement Board (2017) and the Oregon Natural Resources Conservation Service (2018) have helped to launch studies on OSU lands to restore native habitat in wet pastures and oak woodlands using grazers, fire, and beaver. </p>

                    <h3>2020-present</h3>
										<p>Two milestones were reached in 2020: publication of Volume 2 of the <i>Flora of Oregon</i>, and the launching of OregonFlora's new website based on the Symbiota platform. This introduced the Grow Natives tool - specialized content for gardeners and landscapers using native species. </p>
<p> Development of a new interactive rare plant guide began in late 2022; it will function similar to the Grow Natives tool when launched on the OregonFlora website.</p>
<p> OregonFlora became a charter member (2022) of The Oregon Native Plant Conservation Coalition (ONPCC), a diverse, regional coalition composed of federal and state agencies, academic institutions, and nonprofits with a history of plant conservation, restoration, and ecological research in Oregon.</p>
<p>Publication of Volume 3 of the <i>Flora of Oregon</i> remains the priority for OregonFlora. The knowledge gained from this floristic project will drive far-reaching research initiatives, land management practices and activities by people and organizations nationwide.</p>
                </div>

              
            </div>
            <div class="row two-col-row"> 
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h2>Project Organization</h2>
                    <p>OregonFlora is based in the <a href="https://bpp.oregonstate.edu/herbarium" target="_blank">Department of Botany & Plant Pathology</a> (BPP) at Oregon State University. We work closely with the <a href="https://bpp.oregonstate.edu/herbarium" target="_blank">OSU Herbarium</a>.  
                    BPP supports our program by providing indirect costs and office space. 
                    Grants and charitable donations fund 100% of the OregonFlora salaries, employee benefits, and direct operating expenses. 
                    The <a href="npsoregon.org/" target="_blank">Native Plant Society of Oregon</a> has been a sponsor of OregonFlora since its inception in 1994. 
                    The <a href="https://agresearchfoundation.oregonstate.edu/" target="_blank">Agricultural Research Foundation</a>, a nonprofit 501(c)3 affiliated with OSU, serves as our fiscal agent. </p>
                </div>                
            </div>
        </div> <!-- .inner-content -->
    </section>
</div> <!-- .info-page -->

<?php
include( $serverRoot . "/footer.php" );
?>

</body>
</html>