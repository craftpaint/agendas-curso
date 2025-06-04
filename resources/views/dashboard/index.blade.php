<!-- Layout container -->
<div class="layout-page">
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Ajax Sourced Server-side -->
            <div class="card">

            </div>
            <!--/ Ajax Sourced Server-side -->
        </div>
        <!-- / Content -->
    </div>
    <!-- Content wrapper -->
</div>
<script>
    const roles = JSON.parse('<?php echo json_encode($roles); ?>');
    const estados = JSON.parse('<?php echo json_encode($estados); ?>');
    const servicios_liquidador = JSON.parse('<?php echo json_encode($servicios_liquidador); ?>');
    const agentes = JSON.parse('<?php echo json_encode($agentes); ?>');
</script>