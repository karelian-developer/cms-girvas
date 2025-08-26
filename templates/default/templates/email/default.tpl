<style>
  .email-wrapper {
    background-color: #EAEAEA;
    padding-top: 40px;
    padding-bottom: 40px;
    width: 100%;
  }
  .table-wrapper {
    background-color: #FFFFFF;
    padding-top: 20px;
    padding-bottom: 20px;
    padding-left: 20px;
    padding-right: 20px;
    margin: 0 auto;
    width: 600px;
  }
  .table {
    width: 100%;
  }
  .email {
    font: 'Arial', 'Helvetica', 'sans-serif';
  }
  .email__title {
    font-size: 24px;
    font-weight: 700;
    color: #232323;
  }
  .email__content {
    font-size: 16px;
    color: #232323;
  }
  .email__copyright {
    font-size: 14px;
    color: #232323;
  }
</style>
<div class="email-wrapper">
  <div class="table-wrapper">
    <table class="table email">
      <tr class="table__row row">
        <td class="table__ceil email__title">{EMAIL_TITLE}</td>
      </tr>
      <tr class="table__row row">
        <td class="table__ceil" style="height: 20px;"></td>
      </tr>
      <tr class="table__row row">
        <td class="table__ceil email__content">{EMAIL_CONTENT}</td>
      </tr>
      <tr class="table__row row">
        <td class="table__ceil" style="height: 20px;"></td>
      </tr>
      <tr class="table__row row">
        <td class="table__ceil email__copyright">{EMAIL_COPYRIGHT}</td>
      </tr>
    </table>
  </div>
</div>