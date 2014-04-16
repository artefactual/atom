<div class="modal fade hide" id="basisModal" tabindex="-1" 
  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Set/Modify Rights</h3>
  </div>
  <div class="modal-body">
    <form action="/right/post" method="post" role="form">
      <div class="form-group">
        <input type="hidden" name="id" value="<?php echo $right->id ?>">

        <label for="basis" class="control-label">Basis</label>
        <div class="">
          <select name="basis">
            <?php foreach ($basisOptions as $option): ?>
              <option value="<?php echo $option['value'] ?>" <?php echo $option['selected'] ?>><?php echo $option['text'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="startDate" class="control-label">Start</label>
        <div class="">
          <input type="date" class="" name="startDate" id="startDate" value="<?php echo $right->startDate ?>" placeholder="year-mm-dd">
        </div>

        <label for="endDate" class="control-label">End</label>
        <div class="">
          <input type="date" class="" name="endDate" id="endDate" value="<?php echo $right->endDate ?>" placeholder="year-mm-dd">
        </div>
      </div>

      <div class="form-group">
        <label for="rightsNote" class="control-label">Right Notes</label>
        <div class="">
          <input type="text" class="" id="rightsNote" name="rightsNote" value="<?php echo $right->rightsNote ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="rightsHolderId" class="control-label">Rights Holder</label>
        <div class="">
          <input type="text" class="" id="rightsHolderId" name="rightsHolderId" value="<?php echo $right->rightsHolder->id ?>">
        </div>
      </div>

      <!-- COPYRIGHT -->
      <div class="form-group copyright basis-group">
        <label for="copyrightJurisdiction" class="control-label">Copyright Jurisdiction</label>
        <div class="">
          <?php echo $countries->render('copyrightJurisdiction', $right->copyrightJurisdiction); ?>
        </div>

        <label for="copyrightStatusId" class="control-label">Copyright Status</label>
        <div class="">
          <select name="copyrightStatusId" id="copyrightStatusId">
            <?php foreach ($copyrightStatusOptions as $option): ?>
              <option value="<?php echo $option['value'] ?>" <?php echo $option['selected'] ?>><?php echo $option['text'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <label for="copyrightNote" class="control-label">Copyright Note</label>
        <div class="">
          <textarea rows="3" name="copyrightNote" id="copyrightNote"><?php echo $right->copyrightNote; ?></textarea>
        </div>

      </div>

      <!-- LICENSE -->
      <div class="form-group license basis-group">
        <label for="licenseIdentifier" class="control-label">Liceense Identifier</label>
        <div class="">
          <textarea rows="3" name="licenseIdentifier" id="licenseIdentifier"><?php echo $right->licenseIdentifier; ?></textarea>
        </div>

        <label for="licenseTerms" class="control-label">License Note</label>
        <div class="">
          <textarea rows="3" name="licenseTerms" id="licenseTerms"><?php echo $right->licenseTerms; ?></textarea>
        </div>

        <label for="licenseNote" class="control-label">License Note</label>
        <div class="">
          <textarea rows="3" name="licenseNote" id="licenseNote"><?php echo $right->licenseNote; ?></textarea>
        </div>
      </div>

      <!-- STATUTE -->
      <div class="form-group statute basis-group">
        <label for="statuteJurisdiction" class="control-label">Statute Jurisdiction</label>
        <div class="">
          <?php echo $countries->render('statuteJurisdiction', $right->statuteJurisdiction); ?>
        </div>

        <label for="statuteCitation" class="control-label">Statute Citation</label>
        <div class="">
          <textarea rows="3" name="statuteCitation" id="statuteCitation"><?php echo $right->statuteCitation; ?></textarea>
        </div>

        <label for="determinationDate" class="control-label">Statute Determination Date</label>
        <div class="">
          <input type="date" class="" id="determinationDate" name="determinationDate" value="<?php echo $right->statuteDeterminationDate ?>" placeholder="year-mm-dd">
        </div>
      </div>

    </form>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary">Save changes</button>
  </div>
</div>
