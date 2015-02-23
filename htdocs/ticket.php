<?php

$title = 'Ticket '.$_GET['id'];
include('../lib/core/loader.php');

if (empty($_GET['id'])) {
    include('../lib/frontend/top.php');
    echo '<h2>404 - Invalid ticket</h2>';
    include('../lib/frontend/bottom.php');
    die();
}

$report = reportGet($_GET['id']);

if (!$report) {
    include('../lib/frontend/top.php');
    echo '<h2>404 - Invalid ticket</h2>';
    include('../lib/frontend/bottom.php');
    die();
}

// View or download evidence
if(isset($_GET['action']) && $_GET['action'] == 'DownloadEvidence' && is_numeric($_GET['EvidenceID'])) {
    if ($eml = evidenceGet($_GET['EvidenceID'])) {
        header('Content-Type: message/rfc822');
        header('Content-Transfer-Encoding: Binary'); 
        header("Content-disposition: attachment; filename=\"${_GET['EvidenceID']}.eml\""); 
        echo $eml['Data'];
        die();
    }
} 
if (isset($_GET['action']) && $_GET['action'] == 'ViewEvidence' && is_numeric($_GET['EvidenceID'])) {
    if ($eml = evidenceGet($_GET['EvidenceID'])) {
        include('../lib/frontend/top.php');
        echo '<pre>';
        echo htmlentities($eml['Data']);
        echo '</pre>';
        include('../lib/frontend/bottom.php');
        die();
    }
}

include('../lib/frontend/top.php');

$labelClass = array(
    'ABUSE'=>'warning',
    'INFO'=>'info',
    'ALERT'=>'danger'
);
?>

<dl class="dl-horizontal">
    <dt>IP address</dt>
    <dd><?php echo "<a href='reports.php?IP=${report['IP']}'>${report['IP']}</a>"; ?></dd>

    <?php 
        $reverse = gethostbyaddr($report['IP']);
        if ($reverse != $report['IP'] && $reverse !== false) {
    ?>
    <dt>Reverse DNS</dt>
    <dd><?php echo gethostbyaddr($report['IP']); ?></dd>
    <?php } ?>

    <?php if (!empty($report['Domain'])) { ?>
    <dt>Domain</dt>
    <dd><?php echo $report['Domain']; ?></dd>
    <?php } ?>

    <?php if (!empty($report['URI'])) { ?>
    <dt>URI</dt>
    <dd><?php echo $report['URI']; ?></dd>
    <?php } ?>

    <dt>Customer Code</dt>
    <dd><?php echo "<a href='reports.php?CustomerCode=${report['CustomerCode']}'>${report['CustomerCode']}</a>"; ?></dd>

    <dt>Customer Name</dt>
    <dd><?php echo "<a href='reports.php?CustomerName=${report['CustomerName']}'>${report['CustomerName']}</a>"; ?></dd>

    <dt>Customer Contact(s)</dt>
    <dd><?php echo $report['CustomerContact']; ?></dd>

    <dt>Classification</dt>
    <dd><?php echo "<a href='reports.php?Class=${report['Class']}'>${report['Class']}</a>"; ?></dd>

    <dt>Source</dt>
    <dd><?php echo "<a href='reports.php?Source=${report['Source']}'>${report['Source']}</a>"; ?></dd>

    <dt>Type</dt>
    <dd><?php echo "<span class='label label-${labelClass[$report['Type']]}'><a href='reports.php?Type=${report['Type']}'>${report['Type']}</a></span>"; ?></dd>

    <dt>First Seen</dt>
    <dd><?php echo date("d-m-Y H:m", $report['FirstSeen']); ?></dd>

    <dt>Last Seen</dt>
    <dd><?php echo date("d-m-Y H:m", $report['LastSeen']); ?></dd>

    <dt>Report Count</dt>
    <dd><?php echo $report['ReportCount']; ?></dd>

    <dt>Notified Count</dt>
    <dd><?php echo $report['NotifiedCount']; ?></dd>

</dl>

<h2>Information</h2>

<dl class="dl-horizontal">
<?php
    $info_array = json_decode($report['Information'], true);
    foreach($info_array as $field => $value) {
        echo "<dt>${field}</dt>";
        echo "<dd>${value}</dd>";
    }
?>
</dl>

<h2>Evidence</h2>

<table class="table table-striped table-condensed">
    <thead>
        <tr>
          <th width='200'>Date</th>
          <th width='300'>Sender</th>
          <th>Subject</th>
          <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<?php
$evidences = evidenceList($_GET['id']);

foreach($evidences as $nr => $evidence) {
    echo "
        <tr>
          <td>${evidence['LastModified']}</td>
          <td>${evidence['Sender']}</td>
          <td>${evidence['Subject']}</td>
          <td>
                <div class='btn-group pull-right'>
                    <a href='?action=ViewEvidence&EvidenceID=${evidence['ID']}&id=${_GET['id']}' class='btn btn-default btn-sm' title='View EML file' target='_blank'>View<a/>
                    <a href='?action=DownloadEvidence&EvidenceID=${evidence['ID']}&id=${_GET['id']}' class='btn btn-default btn-sm' title='Download EML file'>Download<a/>
                </div>
          </td>
        </tr>
    ";
}
?>
    </tbody>
</table>
<?php // End of Evidence section ?>

<?php
// Start Notes section
if (NOTES == true) {

if(!empty($_POST['action']) && $_POST['action'] == 'addNote') {
    if(!empty($_SERVER['REMOTE_USER'])) {
        $submittor = $_SERVER['REMOTE_USER'];
    } else {
        $submittor = "Abusedesk";
    }
    if (!empty($_POST['Note'])) reportNoteAdd($submittor, $_POST['id'], $_POST['Note']);
}
if(!empty($_GET['action']) && $_GET['action'] == 'delNote' && is_numeric($_GET['noteid'])) {
    reportNoteDelete($_GET['noteid']);
}
?>


<h2>Notes</h2>

<table class="table table-striped table-condensed">
    <thead>
        <tr>
          <th width='200'>Date</th>
          <th width='300'>Submittor</th>
          <th>Note</th>
          <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<?php
$filter  = "AND ReportID = ${_GET['id']} ORDER BY Timestamp DESC";
$notes = reportNoteList($filter);

foreach($notes as $nr => $note) {
    echo "
        <tr>
          <td>".date("Y-m-d H:i:s", $note['Timestamp'])."</td>
          <td>${note['Submittor']}</td>
          <td>${note['Text']}</td>
          <td>
              <div class='btn-group pull-right'>
                  <a href='?action=delNote&id=${_GET['id']}&noteid=${note['ID']}' class='btn btn-default btn-sm' title='Delete note' onclick='return confirm(\"Are you sure you want to delete this note?\");'>Delete</a>
              </div>
          </td>
        </tr>
    ";
}
?>
    </tbody>
</table>

<br>

<form method='POST' action="ticket.php?id=<?php echo $_GET['id']; ?>">
    <input type='hidden' name='action' value='addNote'>
    <input type='hidden' name='id' value='<?php echo $_GET['id']; ?>'>
    <div class="row">
        <div class="col-md-6 form-group form-group-sm">
            <label for='Ticket'>Add note</label>
            <textarea rows="4" cols="80" name='Note'></textarea>
        </div>
        <div class="col-md-12">
            <button type='submit' class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<?php } // End Notes section ?>