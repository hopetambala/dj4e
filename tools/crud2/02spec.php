<?php

if ( ! isset($_GET['assn'] ) ) die('No assignment');

$assn = $_GET['assn'];

// if ( strpos($assn, '02') !== 0 ) die('Bad format');
if ( strpos($assn, '.php') === false ) die('Bad format');

$SPEC_ONLY = true;
require_once($assn);

if ( !isset($title_singular) ) {
    die('Fields not set');
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?= $assignment_type ?>: <?= $title_singular ?> Database CRUD</title>
<style>
li { padding: 5px; }
pre {padding-left: 2em;}
</style>
</head>
<body style="margin-left:5%; margin-bottom: 60px; margin-right: 5%; font-family: sans-serif;">
<h1><?= $assignment_type ?>: <?= $title_singular ?> Database CRUD</h1>
<p>
In this <?= $assignment_type_lower ?> you will build a web based application to
track data about <?= strtolower($title_plural) ?> <?= $assignment_examples ?>.
Only logged in users will be able to add a <?= strtolower($title_singular) ?> record.
Each of the <?= strtolower($title_singular) ?> will be owned by the logged-in
user that created the item.  Only the owning user will be able to edit or
deletes <?= strtolower($title_singular) ?> entries that belong to them.
</p>
<?php if ( $assignment_type == 'Exam' || $assignment_type == "Sample Exam" ) { ?>
<h1><?= $assignment_type ?> Rules
</h1>
<p>
<?php
    if ( $assignment_type == "Sample Exam" ) {
        echo('<b>(If this were a real exam)</b> ');
    }
?>
In order for us to consider your exam for grading, you must read the
statement below and if you agree with the statement sign and date below
and turn the entire exam packet in at the end of the exam.
If you do not return this signed exam sheet before you leave the room,
your exam will not be graded and you will receive a zero on this exam.
</p>
<div style="border:2px solid black; padding: 5px; margin: 5px; width:100%"><b>
This examination represents my own work and I have neither
received nor given anyone any aid on this examination.
<pre>

SIGNATURE: ________________________________________________

PRINT NAME: __________________________________________________

Date:  _______________
</pre>
</b>
</div>
<p>
<?php
    if ( $assignment_type == "Sample Exam" ) {
        echo('If this were a real exam, it would be ');
    } else {
        echo('This exam is');
    }
?>
open-book, open notes, open network, and you can use
any of your prior work for the class to complete the exam.
You cannot listen to audio or watch any videos during the exam.
You cannot get any help from any other person. You also cannot give
any help to any other person. We will grade partial
solutions so you should hand in your work at the end of the
exam even is it is not 100% complete. Please do not discuss the
nature of the exam with anyone except the teaching staff until
we tell you that all students have completed the exam.
</p>
<p>
<b>Note:</b> You must upload a ZIP file of your application to the LMS
to receive full credit for the exam even if the autograder gives you a perfect score.
If your application is not working at the end of the exam period (even
if the autograder still is giving you a score of zero), make sure to ZIP
up your code and upload it to the LMS so we can hand grade your exam
and give you a more appropriate score than the autograder.
</p>
<?php } else { ?>
<h1>Resources</h1>
<p>There are several resources you might find useful:
<ul>
<li>Recorded lectures, sample code and chapters from
<a href="http://www.dj4e.com" target="_blank">www.dj4e.com</a>
</ul>
<li>
The sample code that we covered in class and used in previous assignments.
<pre>
<a href="https://github.com/csev/dj4e-samples/tree/master/samples" target="_blank">https://github.com/csev/dj4e-samples/tree/master/samples</a>
</pre>
<?php if ( isset($particular) ) { ?>
In particular, pay attention to the <?= $particular ?> examples.
<?php } ?>
</li>
</ul>
<?php } ?>
<?php if ( $reference_implementation ) { ?>
<h2>Sample Implementation</h2>
<p>
You can experiment with a reference implementation at:
</p>
<p>
<a href="<?= $reference_implementation ?>" target="_blank"><?= $reference_implementation ?></a>
</p>
<p>
You can use your github account to log in to the system.
</p>

<?php } ?>
<h2 clear="all">Specifications / Tasks</h2>
<p>
Here are some general specifications for this <?= $assignment_type_lower ?>:
<ul>
<li>
The auto-grader-required <b>meta</b> tag must be in the head area for all of the pages
for this <?= $assignment_type_lower ?>.
</li>
<li>
This can be added as a new application to your <b><?= $base_project ?></b> project.
You do not have to remove
existing applications, simply add a new <b><?= $main_lower_plural ?></b> application.
Activate any virtual environment you need (if any) and go into your <b>django_projects</b> folder
and start a new application in your <b><?= $base_project ?></b> project (this project already should have
<?php if ($main_lower_plural == 'autos') { ?>
a 'home' application from a previous assignment):
<?php } else { ?>
'home' and 'autos' applications from previous assignments):
<?php } ?>
<pre>
    workon django2  # as needed
    cd ~/django_projects/<?= $base_project ?>  
    python3 manage.py startapp <?= $main_lower_plural ?>
</pre>
</li>
<li>
Edit the <b><?= $base_project ?>/<?= $base_project ?>/settings.py</b> to update the INSTALLED_APPS:
<pre>
INSTALLED_APPS = [
    'django.contrib.admin',
    ...
    'home.apps.HomeConfig',
    'autos.apps.AutosConfig',
<?php if ($main_lower_plural != 'autos') { ?>
    '<?= $main_lower_plural ?>.apps.<?= $title_plural ?>Config',    &lt;---- Add this
<?php } ?>
]
</pre>
</li>
<li>
<p>
Use the following as your <b><?= $main_lower_plural ?>/models.py</b> file.
<pre>
from django.db import models
from django.core.validators import MinLengthValidator
from django.conf import settings

class <?= $main_title ?>(models.Model):
    name = models.CharField(
            max_length=200,
            validators=[MinLengthValidator(2, "Name must be greater than 1 character")]
    )
<?php
foreach($fields as $field ) {
    if ( $field['type'] == 'i' ) {
        echo('    '.$field['name']." = models.PositiveIntegerField()\n");
    } else {
        echo('    '.$field['name']." = models.CharField(max_length=300)\n");
    }
}
?>
    comments = models.ManyToManyField(settings.AUTH_USER_MODEL, through='Comment', 
    related_name='<?= $main_lower ?>_comments')
    owner = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Shows up in the admin list
    def __str__(self):
        return self.name

class Comment(models.Model) :
    text = models.TextField(
        validators=[MinLengthValidator(3, "Comment must be greater than 3 characters")]
    )

    <?= $main_lower ?> = models.ForeignKey(<?= $main_title ?>, on_delete=models.CASCADE)
    owner = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)

    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Shows up in the admin list
    def __str__(self):
        if len(self.text) &lt; 15 : return self.text
        return self.text[:11] + ' ...'
</pre>
<li>
Once your <b>models.py</b> is set, run the python commands to perform the migrations.
</li>
<li>
You should not need to make any changes to <b>home/templates/main.html</b>.
</li>
<li>
You should add a route to your <b><?= $base_project ?>/urls.py</b> as follows:
<pre>
urlpatterns = [
    ...
    path('admin/', admin.site.urls),
    path('accounts/', include('django.contrib.auth.urls')),
    ...
    path('<?= $main_lower_plural ?>/', include('<?= $main_lower_plural ?>.urls')),
]
</pre>
</li>
<li>
Create the <b><?= $main_lower_plural ?>/urls.py</b> file to add routes for the list,
edit, and delete pages for <?= $main_lower_plural ?>.
Also add the routes to handle the comments views.
</li>
<li>
You can copy and adapt the <b><?= $main_lower_plural ?>/views.py</b> from a previous assignment.
You can also copy and adapt your templates from a previous assignment to produce 
<b><?= $main_lower_plural ?>/templates</b>.
</li>
<li>
Edit <b>base_menu.html.py</b> template so you have a <b>Create <?= $main_title ?></b> link
that appears only when the user is logged in.
</li>
<li>
If you have not already done so, create a superuser so you can test the admin interface and log in to the application.
</li>
</ul>
<h1>Using the Autograder</h1>
<p>
This <?= $assignment_type_lower ?> will be automatically graded.  You will have
unlimited attempts in the autograder until the deadline for submission.   Your web server will need an
Internet-accessible URL so you can submit it for autograding.  You can do this either using
<a href="https://www.pythonanywhere.com" target="_blank">PythonAnywhere</a> or
<a href="https://www.ngrok.com" target="_blank">Ngrok</a>.
Instructions for using ngrok are available at:
</p>
<p>
<a href="http://www.dj4e.com/ngrok" target="_blank">http://www.dj4e.com/ngrok</a>
</p>
<p>
Please see the process for handing in the <?= $assignment_type_lower ?> at the end of this document.
</p>
<p>
<b>Important:</b> The autograder will demand that your &lt;meta&gt; tag is in the
head area of your document.  If the autograder does not find the tag,
it will run all the tests but will not treat the grade as official.
</p>
<h1>What To Hand In</h1>
<p>
This <?= $assignment_type_lower ?> will be autograded by a link that you will be provided with in the LMS
system.   When you launch the autograder, it will prompt for a web-accessible URL
where it can access your web application.
<?php if ( $assignment_type == 'Exam') { ?>
Please also have in a ZIP of your source code (entire project)
in case there is a need to verify your work or assign partial credit.
</p>
<p>
If you are doing your work on PythonAnywhere, create a ZIP file as follows:
<pre>
cd ~/django_projects/<?= $base_project ?>
    rm -f <?= $main_lower_plural ?>.zip
    zip -r <?= $main_lower_plural ?>.zip <?= $main_lower_plural ?> -x '*pycache*'
</pre>
Then download the ZIP file from <b>django_projects/<?= $base_project ?></b> using the
Files tab of PythonAnywhere and upload the ZIP file back up to the LMS.  Some browsers
(i.e. Safari on the Mac) automatically extract the ZIP into a folder.  If this
happens simply compress it again to make a ZIP to upload.
</p>
<?php } ?>
</p>
<hr/>
Provided by: <a href="http://www.dj4e.com/" target="_blank">www.dj4e.com</a>
<center>
<?php if ( strpos($assignment_type,'Exam') !== false ) { ?>
Copyright Charles R. Severance - All Rights Reserved
<?php } else { ?>
Copyright Creative Commons Attribution 3.0 - Charles R. Severance
<?php } ?>
</center>
</body>
</html>
