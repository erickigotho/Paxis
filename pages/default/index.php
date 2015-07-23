<h4>Welcome to Paxis Pro!</h4>

<p>You are currently logged in as <?php echo $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']; ?></p>
<p><a data-toggle="modal" class="btn btn-warning" role="button" href="#addRoomModal"><i class="icon-plus icon-white"></i> New Assessment</a></p>

<div id="addRoomModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>New Assessment</h3>
    </div>
  
        <div class="modal-body">
            Are there going to be multiple jobs within a community?
        </div>
        <div class="modal-footer-custom">
            <a href="add_community.html"><button class="btn btn-primary">Yes</button></a>
            <a href="add_property.html"><button class="btn">No</button></a>
        </div>
  
</div>

