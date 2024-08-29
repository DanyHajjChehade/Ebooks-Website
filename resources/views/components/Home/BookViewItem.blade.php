<div class="box">
    <div class="data">
        <img src="imgs/team-01.jpg" alt="" />
        <div class="social">
            <a href="{{ route('cart.index') }}">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <a href="#">
                <i class="fa-solid fa-plus"></i>
            </a>
            <a href="#" data-bs-toggle="modal" data-bs-target="#bookDescriptionModal">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>
        </div>
    </div>
    <div class="info">
        <h3>Name</h3>
        <p>Simple Short Description</p>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="bookDescriptionModal" tabindex="-1" aria-labelledby="bookDescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #2196f3; color: #fff;">
                <h5 class="modal-title" id="bookDescriptionModalLabel">Book Description</h5>
                <button type="button" class="btn-close" style="color: #fff;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="imgs/team-01.jpg" class="img-fluid" alt="Book Image">
                        </div>
                        <div class="col-md-8">
                            <h4>Name</h4>
                            <p>Remaining Quantity: <strong>5</strong></p>
                            <p>Description:</p>
                            <p>Full detailed description of the book goes here...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #2196f3;">
                <button type="button" class="btn btn-secondary" style="background-color: #1787e0;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
