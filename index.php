<?php include("includes/init.php");
$title = "Course Catalog";

$db = open_sqlite_db('secure/catalog.sqlite');


function printresult($entry)
{
?>
  <tr>
    <td><?php echo htmlspecialchars($entry["id"]); ?></td>
    <td><?php echo htmlspecialchars($entry["name"]); ?></td>
    <td><?php echo htmlspecialchars($entry["numcredits"]); ?></td>
    <td><?php echo htmlspecialchars($entry["req"]); ?></td>
    <td><?php echo htmlspecialchars($entry["description"]); ?></td>

  </tr>
<?php
}
$messages = array();
const SEARCH_F = [
  "id" => "By Course ID",
  "name" => "By Course Name",
  "req" => "By Requirement Fulfilled"
];
if (isset($_GET['search']) and isset($_GET['field'])) {
  $search = TRUE;
  $field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING);
  if (in_array($field, array_keys(SEARCH_F))) {
    $search_f = $field;
  } else {
    array_push($messages, "You cannot search with this field.");
    $search = FALSE;
  }
  $searched = filter_input(INPUT_GET, 'searched', FILTER_SANITIZE_STRING);
  $searched = trim($searched);
} else {
  // No search provided, so set the product to query to NULL
  $search = FALSE;
  $field = NULL;
  $searched = NULL;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Course Catalog for Information Science, Systems, and Technology major</title>
  <link rel="stylesheet" type="text/css" href="styles/site.css" media="all" />
</head>

<header>
  <h1 class="title">Course Catalog for Information Science, Systems, and Technology major</h1>
</header>

<body>
  <main>
    <h2><?php echo $title; ?></h2>
    <p>Welcome to the course catalog for ISST (Information, Science, Systems, and Technology) majors.</p>

    <?php
    foreach ($messages as $message) {
      echo "<p><strong>" . htmlspecialchars($message) . "</strong></p>\n";
    }
    ?>

    <form id="searchForm" action="index.php" method="get">
      <select name="field">
        <option value="" selected disabled>Search By</option>
        <?php
        foreach (SEARCH_F as $field => $label) {
        ?>
          <option value="<?php echo $field; ?>"><?php echo $label; ?></option>
        <?php
        }
        ?>
      </select>
      <input type="text" name="search" />
      <button type="submit">Search</button>
    </form>

    <?php
    if ($search) {
    ?>
      <h2>Search Results</h2>
    <?php
      $sql = "SELECT * FROM courses WHERE " . $search_field . " LIKE '%' || :searched || '%'";
      $params = array(
        ':searched' => $searched
      );
    } else {
    ?>
      <h2>All Courses</h2>
    <?php

      $sql = "SELECT* FROM courses;";
      $params = array();
    }

    $result = exec_sql_query($db, $sql, $params);
    $records = $result->fetchAll();
    if (isset($records) and !empty($records)) {
    ?>
      <table>
        <tr>
          <th>Course ID</th>
          <th>Course Name</th>
          <th># of Credits</th>
          <th>Fulfills Requirement</th>
          <th>Description</th>
        </tr>
        <?php
        foreach ($records as $entry) {
          printresult($entry);
        }

        ?>
      </table>
    <?php
    } else {
      echo "<p>No courses match your search</p>";
    }

    ?>
    </table>
    <?php
    ?>
    <div>
      <h2>Fill out this form to add a course to the catalog:</h2>
      <form id="courseform" method="post" action="index.php" novalidate>
        <div>
          <label for="id_field">Course ID:</label>
          <input id="id_field" type="text" name="form_id" required />
        </div>
        <div>
          <label for="name_field">Course Name:</label>
          <input type="text" id="name_field" name="form_name" required />
        </div>
        <div>
          <label for="cred_field"># of Credits:</label>
          <input type="number" id="cred_field" name="form_cred" min=1 max=5 required />
        </div>
        <div>
          <label for="req_field">Requirement fulfilled:</label>
          <input type="text" id="req_field" name="form_req" required />
        </div>
        <div>
          <label for="description_field">Description:</label>
          <input type="text" id="description_field" name="form_description" required />
        </div>

        <div class="button">
          <span></span>
          <input type="submit" value="Add Course" />
        </div>

      </form>
    </div>
  </main>

</body>

<footer>
  Please visit the <a href="https://infosci.cornell.edu/undergraduate/info-sci-majors/bs-information-science-systems-and-technology/degree-requirements">Office Cornell website</a> for more information.
</footer>

</html>
